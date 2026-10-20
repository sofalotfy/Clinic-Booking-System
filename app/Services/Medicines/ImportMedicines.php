<?php

namespace App\Services\Medicines;

use App\Models\Medicine;
use App\Models\MedicineImport;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use OpenSpout\Reader\CSV\Reader as CsvReader;
use OpenSpout\Reader\ReaderInterface;
use OpenSpout\Reader\XLSX\Reader as XlsxReader;
use Throwable;

/**
 * Syncs the medicine catalog with an uploaded drug price sheet: rows whose
 * trade name already exists are updated, new ones are added, and medicines
 * missing from the sheet are left untouched.
 */
class ImportMedicines
{
    private const CHUNK = 500;

    private const MAX_REPORTED_ERRORS = 50;

    /**
     * Sheet header => medicine column. Headers are compared after
     * normalizeHeader(), so case, extra spaces and parenthesized notes such
     * as "(ج.م)" do not matter. The column names themselves are accepted too.
     */
    private const HEADERS = [
        'الاسم التجاري بالإنجليزية' => 'name',
        'الاسم التجاري لصنف الدواء بالعربية' => 'name_ar',
        'المادة الفعالة' => 'active_ingredient',
        'السعر الحالي للعلبة' => 'price',
        'عدد الوحدات الكبرى' => 'major_units',
        'سعر الوحدة' => 'unit_price',
        'عدد الوحدات الصغرى' => 'minor_units',
        'تصنيف الدواء' => 'category',
        'الشركة المنتجة' => 'company',
        'الحجم' => 'size',
        'التركيز' => 'strength',
        'رمز الباركود' => 'barcode',
        'dosage_form' => 'dosage_form',
        'نوع الدواء' => 'origin',
        'رابط صورة الدواء' => 'image_url',
    ];

    private const DECIMALS = ['price', 'unit_price'];

    private const INTEGERS = ['major_units', 'minor_units'];

    public static function execute(string $path, string $fileName, ?User $uploader = null): MedicineImport
    {
        $reader = self::readerFor($fileName);

        try {
            $reader->open($path);
        } catch (Throwable $exception) {
            throw ValidationException::withMessages(['file' => 'The file could not be read: '.$exception->getMessage()]);
        }

        try {
            return DB::transaction(fn () => self::sync($reader, $fileName, $uploader));
        } finally {
            $reader->close();
        }
    }

    private static function sync(ReaderInterface $reader, string $fileName, ?User $uploader): MedicineImport
    {
        $existing = array_flip(Medicine::withTrashed()->pluck('match_key')->all());

        $stats = ['total_rows' => 0, 'created_count' => 0, 'updated_count' => 0, 'skipped_count' => 0, 'duplicate_count' => 0];
        $errors = [];
        $seen = [];
        $batch = [];
        $columns = null;

        // Only the first sheet is read
        foreach ($reader->getSheetIterator() as $sheet) {
            foreach ($sheet->getRowIterator() as $index => $row) {
                $cells = $row->toArray();

                if (self::isBlank($cells)) {
                    continue;
                }

                if ($columns === null) {
                    $columns = self::mapHeaders($cells);

                    continue;
                }

                $stats['total_rows']++;
                $attributes = self::toAttributes($cells, $columns);

                if (! $attributes['name']) {
                    $stats['skipped_count']++;
                    self::report($errors, "Row {$index}: missing English trade name.");

                    continue;
                }

                $key = Medicine::matchKey($attributes['name']);

                if (isset($seen[$key])) {
                    $stats['duplicate_count']++;
                    self::report($errors, "Row {$index}: \"{$attributes['name']}\" appears earlier in the sheet, ignored.");

                    continue;
                }

                $seen[$key] = true;
                isset($existing[$key]) ? $stats['updated_count']++ : $stats['created_count']++;

                $batch[] = ['match_key' => $key, ...$attributes];

                if (count($batch) >= self::CHUNK) {
                    self::flush($batch);
                }
            }

            break;
        }

        if ($columns === null) {
            throw ValidationException::withMessages(['file' => 'The sheet is empty.']);
        }

        self::flush($batch);

        return MedicineImport::create([
            ...$stats,
            'uploaded_by' => $uploader?->id,
            'file_name' => $fileName,
            'errors' => $errors ?: null,
        ]);
    }

    private static function flush(array &$batch): void
    {
        if (! $batch) {
            return;
        }

        // Sheet columns only, so is_active and soft deletes set by admins stick
        Medicine::upsert($batch, ['match_key'], array_values(self::HEADERS));

        $batch = [];
    }

    private static function readerFor(string $fileName): ReaderInterface
    {
        return match (Str::lower(pathinfo($fileName, PATHINFO_EXTENSION))) {
            'xlsx' => new XlsxReader(),
            'csv' => new CsvReader(),
            default => throw ValidationException::withMessages(['file' => 'Only .xlsx and .csv files are supported.']),
        };
    }

    /**
     * @return array<int, string> cell index => medicine column
     */
    private static function mapHeaders(array $cells): array
    {
        $known = [];

        foreach (self::HEADERS as $header => $column) {
            $known[self::normalizeHeader($header)] = $column;
            $known[self::normalizeHeader($column)] = $column;
        }

        $columns = [];

        foreach ($cells as $index => $cell) {
            $column = $known[self::normalizeHeader((string) $cell)] ?? null;

            if ($column && ! in_array($column, $columns, true)) {
                $columns[$index] = $column;
            }
        }

        if (! in_array('name', $columns, true)) {
            throw ValidationException::withMessages([
                'file' => 'The first row must contain the column "الاسم التجاري بالإنجليزية" (English trade name).',
            ]);
        }

        return $columns;
    }

    private static function normalizeHeader(string $header): string
    {
        return Str::of($header)
            ->replaceMatches('/\(.*?\)/u', '')
            ->replace('_', ' ')
            ->squish()
            ->lower()
            ->toString();
    }

    private static function toAttributes(array $cells, array $columns): array
    {
        $attributes = array_fill_keys(array_values(self::HEADERS), null);

        foreach ($columns as $index => $column) {
            $value = $cells[$index] ?? null;

            $attributes[$column] = match (true) {
                in_array($column, self::DECIMALS, true) => self::number($value),
                in_array($column, self::INTEGERS, true) => self::integer($value),
                default => self::text($value),
            };
        }

        return $attributes;
    }

    private static function text($value): ?string
    {
        if ($value === null) {
            return null;
        }

        // Numeric cells such as barcodes come back as floats, keep every digit
        if (is_float($value) && floor($value) === $value) {
            $value = sprintf('%.0f', $value);
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format('Y-m-d');
        }

        $value = Str::squish((string) $value);

        return $value === '' ? null : $value;
    }

    private static function number($value): ?float
    {
        $value = is_string($value) ? str_replace([',', ' '], '', $value) : $value;

        return is_numeric($value) ? round((float) $value, 2) : null;
    }

    private static function integer($value): ?int
    {
        $number = self::number($value);

        return $number === null ? null : (int) $number;
    }

    private static function isBlank(array $cells): bool
    {
        foreach ($cells as $cell) {
            if ($cell instanceof \DateTimeInterface || trim((string) $cell) !== '') {
                return false;
            }
        }

        return true;
    }

    private static function report(array &$errors, string $message): void
    {
        if (count($errors) < self::MAX_REPORTED_ERRORS) {
            $errors[] = $message;
        }
    }
}
