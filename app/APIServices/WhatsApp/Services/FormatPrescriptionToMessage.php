<?php

namespace App\APIServices\WhatsApp\Services;

use App\Models\Prescription;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class FormatPrescriptionToMessage
{
    /**
     * Right-to-left mark. Every line starts with one: without it a line that
     * begins with an English medicine name (or an emoji, which has no
     * direction of its own) is laid out left to right and its numbering and
     * punctuation end up scrambled around the name.
     */
    private const RLM = "\u{200F}";

    private const DIVIDER = '━━━━━━━━━━━━';

    /**
     * The prescription as one WhatsApp message. Sections are separated by a
     * blank line so a long prescription can be split between them.
     */
    public static function execute(Prescription $prescription): string
    {
        $prescription->loadMissing(['appointment', 'medicines', 'labs', 'scans']);

        $sections = array_filter([
            self::header($prescription),
            self::diagnosis($prescription),
            self::medicines($prescription),
            self::labs($prescription),
            self::scans($prescription),
            self::notes($prescription),
            self::consultation($prescription),
        ]);

        return self::rtl(implode("\n\n", $sections));
    }

    private static function header(Prescription $prescription): string
    {
        $lines = ['📋 *الروشتة*'];

        if ($prescription->appointment) {
            $lines[] = 'كشف '.ArabicDateFormatter::format(Carbon::parse($prescription->appointment->date), includeTime: false);
        }

        $lines[] = self::DIVIDER;

        return implode("\n", $lines);
    }

    private static function diagnosis(Prescription $prescription): ?string
    {
        return $prescription->diagnosis
            ? "🩺 *التشخيص*\n".$prescription->diagnosis
            : null;
    }

    private static function medicines(Prescription $prescription): ?string
    {
        if ($prescription->medicines->isEmpty()) {
            return null;
        }

        $items = $prescription->medicines->map(function ($medicine, $index) {
            $lines = [ArabicDateFormatter::toArabicDigits($index + 1).'- *'.$medicine->name.'*'];

            if ($medicine->name_ar) {
                $lines[] = $medicine->name_ar;
            }

            return implode("\n", array_merge($lines, self::labelled([
                'الجرعة' => $medicine->pivot->dose,
                'التكرار' => $medicine->pivot->frequency,
                'المدة' => $medicine->pivot->duration,
                'تعليمات' => $medicine->pivot->instructions,
            ])));
        });

        return "💊 *الأدوية*\n".$items->implode("\n\n");
    }

    private static function labs(Prescription $prescription): ?string
    {
        if ($prescription->labs->isEmpty()) {
            return null;
        }

        $items = $prescription->labs->map(function ($lab, $index) {
            $lines = [ArabicDateFormatter::toArabicDigits($index + 1).'- *'.($lab->name_ar ?: $lab->name).'*'];

            if ($lab->fasting_required) {
                $lines[] = '⚠️ يلزم الصيام قبل التحليل';
            }

            return implode("\n", array_merge($lines, self::labelled([
                'ملاحظات' => $lab->pivot->notes,
                'التحضير' => $lab->preparation,
            ])));
        });

        return "🧪 *التحاليل المطلوبة*\n".$items->implode("\n\n");
    }

    private static function scans(Prescription $prescription): ?string
    {
        if ($prescription->scans->isEmpty()) {
            return null;
        }

        $items = $prescription->scans->map(function ($scan, $index) {
            $lines = [ArabicDateFormatter::toArabicDigits($index + 1).'- *'.($scan->name_ar ?: $scan->name).'*'];

            if ($scan->with_contrast) {
                $lines[] = '⚠️ بصبغة';
            }

            return implode("\n", array_merge($lines, self::labelled([
                'ملاحظات' => $scan->pivot->notes,
                'التحضير' => $scan->preparation,
            ])));
        });

        return "🩻 *الأشعة المطلوبة*\n".$items->implode("\n\n");
    }

    private static function notes(Prescription $prescription): ?string
    {
        return $prescription->notes
            ? "📝 *ملاحظات*\n".$prescription->notes
            : null;
    }

    private static function consultation(Prescription $prescription): ?string
    {
        return $prescription->consultation_date
            ? "📅 *موعد الاستشارة*\n".ArabicDateFormatter::format($prescription->consultation_date, includeTime: false)
            : null;
    }

    /**
     * "label: value" lines for the values that are filled in.
     *
     * @return array<int, string>
     */
    private static function labelled(array $fields): array
    {
        $lines = [];

        foreach ($fields as $label => $value) {
            if ($value !== null && trim((string) $value) !== '') {
                $lines[] = "{$label}: ".trim((string) $value);
            }
        }

        return $lines;
    }

    private static function rtl(string $text): string
    {
        return preg_replace('/^(?=.)/mu', self::RLM, $text);
    }
}
