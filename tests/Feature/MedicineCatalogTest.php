<?php

namespace Tests\Feature;

use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Assistant;
use App\Models\Doctor;
use App\Models\Medicine;
use App\Models\MedicineImport;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use OpenSpout\Common\Entity\Row;
use OpenSpout\Writer\XLSX\Writer;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class MedicineCatalogTest extends TestCase
{
    use RefreshDatabase;

    private const HEADERS = [
        'الاسم التجاري لصنف الدواء بالعربية', 'الاسم التجاري بالإنجليزية', 'المادة الفعالة', 'السعر الحالي للعلبة (ج.م)',
        'عدد الوحدات الكبرى', 'سعر الوحدة (ج.م)', 'عدد الوحدات الصغرى', 'تصنيف الدواء', 'الشركة المنتجة', 'الحجم',
        'التركيز', 'رمز الباركود', 'dosage_form', 'نوع الدواء', 'رابط صورة الدواء',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);
    }

    public function test_upload_adds_new_medicines_and_maps_every_column(): void
    {
        $admin = $this->makeAdmin(['IMPORT_MEDICINES']);

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.import.store'), ['file' => $this->sheet([
                $this->row('Verzenio 100 mg 14 tabs', 21774, barcode: 6221122334455.0),
                $this->row('Orencia 125 mg soln. s.c. pre-filled syringe', 2658),
            ])])
            ->assertRedirect(route('web-admin.medicines.import'))
            ->assertSessionHas('status');

        $medicine = Medicine::where('name', 'Verzenio 100 mg 14 tabs')->sole();

        $this->assertSame('فيرزينو', $medicine->name_ar);
        $this->assertSame('Abemaciclib', $medicine->active_ingredient);
        $this->assertSame('21774.00', $medicine->price);
        $this->assertSame(2, $medicine->major_units);
        $this->assertSame('10887.00', $medicine->unit_price);
        $this->assertSame(14, $medicine->minor_units);
        $this->assertSame('Antineoplastic', $medicine->category);
        $this->assertSame('Eli lilly', $medicine->company);
        $this->assertSame('100 mg', $medicine->strength);
        $this->assertSame('6221122334455', $medicine->barcode);
        $this->assertSame('tablet', $medicine->dosage_form);
        $this->assertSame('imported', $medicine->origin);
        $this->assertSame('https://example.com/a.png', $medicine->image_url);

        $import = MedicineImport::sole();
        $this->assertSame([2, 2, 0], [$import->total_rows, $import->created_count, $import->updated_count]);
        $this->assertSame($admin->id, $import->uploaded_by);
    }

    public function test_reupload_updates_matching_trade_names_and_keeps_admin_choices(): void
    {
        $inactive = Medicine::create(['name' => 'Verzenio 100 mg 14 tabs', 'price' => 100, 'is_active' => false]);
        $deleted = Medicine::create(['name' => 'Orencia 125 mg', 'price' => 100]);
        $deleted->delete();
        $untouched = Medicine::create(['name' => 'Not In Sheet', 'price' => 5]);

        $import = \App\Services\Medicines\ImportMedicines::execute($this->sheetPath([
            $this->row('  VERZENIO 100 mg   14 tabs ', 21774),
            $this->row('orencia 125 MG', 2658),
            $this->row('Brand New', 10),
        ]), 'drugs.xlsx');

        $this->assertSame([1, 2], [$import->created_count, $import->updated_count]);
        $this->assertSame(4, Medicine::withTrashed()->count());

        $this->assertSame('21774.00', $inactive->fresh()->price);
        $this->assertFalse($inactive->fresh()->is_active);
        $this->assertSame('2658.00', Medicine::withTrashed()->find($deleted->id)->price);
        $this->assertSoftDeleted($deleted);
        $this->assertSame('5.00', $untouched->fresh()->price);
    }

    public function test_duplicates_and_nameless_rows_are_reported(): void
    {
        $import = \App\Services\Medicines\ImportMedicines::execute($this->sheetPath([
            $this->row('Panadol', 10),
            $this->row('PANADOL', 99),
            $this->row('', 5),
        ]), 'drugs.xlsx');

        $this->assertSame([3, 1, 1, 1], [$import->total_rows, $import->created_count, $import->skipped_count, $import->duplicate_count]);
        $this->assertSame('10.00', Medicine::sole()->price);
        $this->assertCount(2, $import->errors);
    }

    public function test_large_sheets_upload_in_chunks_and_are_cleaned_up(): void
    {
        $admin = $this->makeAdmin(['IMPORT_MEDICINES']);
        $rows = array_map(fn ($i) => $this->row("Medicine {$i}", $i), range(1, 300));
        $bytes = file_get_contents($this->sheetPath($rows));
        $uploadId = (string) \Illuminate\Support\Str::uuid();

        // Small pieces so the test file is split several times
        $pieces = str_split($bytes, 4096);

        foreach ($pieces as $index => $piece) {
            $this->actingAs($admin)
                ->post(route('web-admin.medicines.import.chunk'), [
                    'upload_id' => $uploadId,
                    'index' => $index,
                    'chunk' => UploadedFile::fake()->createWithContent('chunk', $piece),
                ], ['Accept' => 'application/json'])
                ->assertOk();
        }

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.import.store'), [
                'upload_id' => $uploadId,
                'chunks' => count($pieces),
                'file_name' => 'big-sheet.xlsx',
            ])
            ->assertRedirect(route('web-admin.medicines.import'))
            ->assertSessionHasNoErrors();

        $this->assertGreaterThan(1, count($pieces));
        $this->assertSame(300, Medicine::count());
        $this->assertSame('big-sheet.xlsx', MedicineImport::sole()->file_name);
        $this->assertDirectoryDoesNotExist(storage_path("app/chunked-uploads/{$uploadId}"));
    }

    public function test_chunked_upload_rejects_missing_pieces_and_bad_ids(): void
    {
        $admin = $this->makeAdmin(['IMPORT_MEDICINES']);
        $uploadId = (string) \Illuminate\Support\Str::uuid();

        $this->actingAs($admin)->post(route('web-admin.medicines.import.chunk'), [
            'upload_id' => $uploadId,
            'index' => 0,
            'chunk' => UploadedFile::fake()->createWithContent('chunk', 'part'),
        ], ['Accept' => 'application/json'])->assertOk();

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.import.store'), ['upload_id' => $uploadId, 'chunks' => 2, 'file_name' => 'x.xlsx'])
            ->assertSessionHasErrors('file');
        $this->assertDirectoryDoesNotExist(storage_path("app/chunked-uploads/{$uploadId}"));

        $this->actingAs($admin)->post(route('web-admin.medicines.import.chunk'), [
            'upload_id' => '../../etc',
            'index' => 0,
            'chunk' => UploadedFile::fake()->createWithContent('chunk', 'part'),
        ], ['Accept' => 'application/json'])->assertUnprocessable();

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.import.store'), ['upload_id' => $uploadId, 'chunks' => 1, 'file_name' => 'x.pdf'])
            ->assertSessionHasErrors('file_name');

        $this->assertSame(0, MedicineImport::count());
    }

    public function test_sheet_without_the_trade_name_column_is_rejected(): void
    {
        $admin = $this->makeAdmin(['IMPORT_MEDICINES']);

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.import.store'), [
                'file' => $this->sheet([['x', 'y']], headers: ['foo', 'bar']),
            ])
            ->assertSessionHasErrors('file');

        $this->assertSame(0, MedicineImport::count());
    }

    public function test_import_command_syncs_a_sheet_from_disk(): void
    {
        $this->artisan('app:import-medicines', ['path' => $this->sheetPath([$this->row('Panadol', 10)])])
            ->expectsOutputToContain('1 added')
            ->assertSuccessful();

        $this->assertSame(1, Medicine::count());
    }

    public function test_admin_crud_and_permissions(): void
    {
        $admin = $this->makeAdmin(['VIEW_ALL_MEDICINES', 'VIEW_SINGLE_MEDICINE', 'CREATE_MEDICINE', 'UPDATE_MEDICINE', 'DELETE_MEDICINE']);

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.store'), ['name' => 'Panadol Extra', 'price' => '45', 'is_active' => '1'])
            ->assertRedirect(route('web-admin.medicines.index'));

        $medicine = Medicine::sole();
        $this->assertSame('panadol extra', $medicine->match_key);

        $this->actingAs($admin)->get(route('web-admin.medicines.index', ['search' => 'panadol']))->assertOk()->assertSee('Panadol Extra');
        $this->actingAs($admin)->get(route('web-admin.medicines.show', $medicine))->assertOk();
        $this->actingAs($admin)->get(route('web-admin.medicines.edit', $medicine))->assertOk();

        $this->actingAs($admin)
            ->post(route('web-admin.medicines.store'), ['name' => ' PANADOL  extra'])
            ->assertSessionHasErrors('name');

        $this->actingAs($admin)
            ->put(route('web-admin.medicines.update', $medicine), ['name' => 'Panadol Extra', 'price' => '50', 'is_active' => '0'])
            ->assertRedirect(route('web-admin.medicines.show', $medicine));
        $this->assertSame('50.00', $medicine->fresh()->price);

        $this->actingAs($admin)->delete(route('web-admin.medicines.destroy', $medicine))->assertRedirect();
        $this->assertSoftDeleted($medicine);

        // Import is a separate permission
        $this->actingAs($admin)->get(route('web-admin.medicines.import'))->assertForbidden();
        $this->actingAs($this->makeAdmin([]))->get(route('web-admin.medicines.index'))->assertForbidden();
    }

    public function test_api_lists_active_medicines_and_requires_clinic_permission(): void
    {
        Medicine::create(['name' => 'Panadol Extra', 'name_ar' => 'بانادول', 'active_ingredient' => 'Paracetamol', 'barcode' => '622111']);
        Medicine::create(['name' => 'Augmentin 1g', 'active_ingredient' => 'Amoxicillin']);
        Medicine::create(['name' => 'Old Drug', 'is_active' => false]);

        $doctor = Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id]);

        $this->actingAs($doctor->user, 'sanctum')->getJson('/api/medicines')
            ->assertOk()
            ->assertJsonPath('medicines.total', 2)
            ->assertJsonMissingPath('medicines.data.0.match_key');

        foreach (['paracetamol', 'بانادول', '622111'] as $term) {
            $this->actingAs($doctor->user, 'sanctum')->getJson('/api/medicines?search='.urlencode($term))
                ->assertJsonPath('medicines.total', 1)
                ->assertJsonPath('medicines.data.0.name', 'Panadol Extra');
        }

        $this->actingAs($doctor->user, 'sanctum')->getJson('/api/medicines/'.Medicine::first()->id)
            ->assertOk()
            ->assertJsonPath('medicine.name', 'Panadol Extra');

        $assistant = $this->makeUser(UserType::ASSISTANT);
        Assistant::create(['doctor_id' => $doctor->id, 'user_id' => $assistant->id]);
        $this->actingAs($assistant, 'sanctum')->getJson('/api/medicines')->assertForbidden();
    }

    private function row(string $name, float $price, ?float $barcode = null): array
    {
        return ['فيرزينو', $name, 'Abemaciclib', $price, 2, $price / 2, 14, 'Antineoplastic', 'Eli lilly', '', '100 mg', $barcode ?? '', 'tablet', 'imported', 'https://example.com/a.png'];
    }

    private function sheetPath(array $rows, array $headers = self::HEADERS): string
    {
        $path = tempnam(sys_get_temp_dir(), 'medicines').'.xlsx';

        $writer = new Writer();
        $writer->openToFile($path);
        $writer->addRow(Row::fromValues($headers));

        foreach ($rows as $row) {
            $writer->addRow(Row::fromValues($row));
        }

        $writer->close();

        return $path;
    }

    private function sheet(array $rows, array $headers = self::HEADERS): UploadedFile
    {
        return new UploadedFile($this->sheetPath($rows, $headers), 'drugs.xlsx', null, null, true);
    }

    private function makeUser(UserType $type): User
    {
        return User::create([
            'name' => 'User '.uniqid(),
            'email' => uniqid('user').'@example.com',
            'phone' => '1'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => $type,
        ]);
    }

    private function makeAdmin(array $permissions): User
    {
        $role = Role::create(['name' => 'Role '.uniqid(), 'guard_name' => 'web', 'doctor_id' => null]);

        foreach ($permissions as $case) {
            $role->givePermissionTo(
                Permission::where('name', constant(AdminPermissionsEnum::class.'::'.$case)->value)
                    ->where('type', PermissionsTypeEnum::ADMIN)
                    ->firstOrFail()
            );
        }

        $admin = $this->makeUser(UserType::ADMIN);
        $admin->assignRole($role);

        return $admin;
    }
}
