<?php

namespace Tests\Feature;

use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Assistant;
use App\Models\Doctor;
use App\Models\Lab;
use App\Models\Role;
use App\Models\Scan;
use App\Models\User;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class LabScanCatalogTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);
    }

    public static function resources(): array
    {
        return [
            'labs' => ['labs', Lab::class, 'LAB', ['category' => 'Hematology', 'sample_type' => 'Blood', 'fasting_required' => '1']],
            'scans' => ['scans', Scan::class, 'SCAN', ['body_part' => 'Chest', 'modality' => 'CT', 'with_contrast' => '1']],
        ];
    }

    #[DataProvider('resources')]
    public function test_admin_can_create_update_and_delete(string $resource, string $model, string $perm, array $fields): void
    {
        $admin = $this->makeAdmin(["CREATE_{$perm}", "UPDATE_{$perm}", "DELETE_{$perm}", "VIEW_ALL_{$perm}S", "VIEW_SINGLE_{$perm}"]);

        $this->actingAs($admin)
            ->post(route("web-admin.{$resource}.store"), [
                'name' => 'Complete Blood Count',
                'name_ar' => 'صورة دم كاملة',
                'code' => 'CBC',
                'is_active' => '1',
                ...$fields,
            ])
            ->assertRedirect(route("web-admin.{$resource}.index"));

        $item = $model::sole();
        $this->assertSame('CBC', $item->code);

        $this->actingAs($admin)->get(route("web-admin.{$resource}.index"))->assertOk()->assertSee('Complete Blood Count');
        $this->actingAs($admin)->get(route("web-admin.{$resource}.show", $item))->assertOk()->assertSee('صورة دم كاملة');
        $this->actingAs($admin)->get(route("web-admin.{$resource}.edit", $item))->assertOk();

        $this->actingAs($admin)
            ->put(route("web-admin.{$resource}.update", $item), ['name' => 'CBC Renamed', 'is_active' => '0'])
            ->assertRedirect(route("web-admin.{$resource}.show", $item));
        $this->assertSame('CBC Renamed', $item->fresh()->name);
        $this->assertFalse($item->fresh()->is_active);

        $this->actingAs($admin)
            ->delete(route("web-admin.{$resource}.destroy", $item))
            ->assertRedirect(route("web-admin.{$resource}.index"));
        $this->assertSoftDeleted($item);
    }

    #[DataProvider('resources')]
    public function test_names_must_be_unique_among_non_deleted(string $resource, string $model, string $perm): void
    {
        $admin = $this->makeAdmin(["CREATE_{$perm}"]);
        $model::create(['name' => 'Duplicate']);

        $this->actingAs($admin)
            ->post(route("web-admin.{$resource}.store"), ['name' => 'Duplicate'])
            ->assertSessionHasErrors('name');

        $model::sole()->delete();

        $this->actingAs($admin)
            ->post(route("web-admin.{$resource}.store"), ['name' => 'Duplicate'])
            ->assertSessionHasNoErrors();
    }

    #[DataProvider('resources')]
    public function test_admin_without_permission_is_forbidden(string $resource): void
    {
        $admin = $this->makeAdmin([]);

        $this->actingAs($admin)->get(route("web-admin.{$resource}.index"))->assertForbidden();
        $this->actingAs($admin)->post(route("web-admin.{$resource}.store"), ['name' => 'X'])->assertForbidden();
    }

    #[DataProvider('resources')]
    public function test_api_lists_only_active_entries_and_searches(string $resource, string $model): void
    {
        $model::create(['name' => 'Lipid Profile', 'name_ar' => 'دهون']);
        $model::create(['name' => 'Liver Function', 'code' => 'LFT']);
        $model::create(['name' => 'Lipase', 'is_active' => false]);

        $doctor = $this->makeDoctor();

        $this->actingAs($doctor->user, 'sanctum')
            ->getJson("/api/{$resource}")
            ->assertOk()
            ->assertJsonPath("{$resource}.total", 2);

        $this->actingAs($doctor->user, 'sanctum')
            ->getJson("/api/{$resource}?search=LFT")
            ->assertOk()
            ->assertJsonPath("{$resource}.total", 1)
            ->assertJsonPath("{$resource}.data.0.name", 'Liver Function');

        $item = $model::where('name', 'Lipid Profile')->first();
        $singular = rtrim($resource, 's');

        $this->actingAs($doctor->user, 'sanctum')
            ->getJson("/api/{$resource}/{$item->id}")
            ->assertOk()
            ->assertJsonPath("{$singular}.name_ar", 'دهون');
    }

    #[DataProvider('resources')]
    public function test_api_requires_clinic_permission(string $resource): void
    {
        $doctor = $this->makeDoctor();

        $assistant = $this->makeUser(UserType::ASSISTANT);
        Assistant::create(['doctor_id' => $doctor->id, 'user_id' => $assistant->id]);

        $this->actingAs($assistant, 'sanctum')->getJson("/api/{$resource}")->assertForbidden();

        $patient = $this->makeUser(UserType::PATIENT);
        $this->actingAs($patient, 'sanctum')->getJson("/api/{$resource}")->assertForbidden();
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

    private function makeDoctor(): Doctor
    {
        $doctor = Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id]);

        return $doctor->load('user');
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
