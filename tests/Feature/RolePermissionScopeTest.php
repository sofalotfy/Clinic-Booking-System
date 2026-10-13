<?php

namespace Tests\Feature;

use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use App\Services\AdminRoles\DeleteRole as AdminDeleteRole;
use App\Services\Roles\DeleteRole as ClinicDeleteRole;
use App\Services\Roles\StoreRole as ClinicStoreRole;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class RolePermissionScopeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);
    }

    public function test_global_roles_only_hold_admin_permissions(): void
    {
        $globalRoles = Role::whereNull('doctor_id')->with('permissions')->get();

        $this->assertNotEmpty($globalRoles);

        foreach ($globalRoles as $role) {
            $this->assertEmpty(
                $role->permissions->where('type', '!=', PermissionsTypeEnum::ADMIN->value),
                "Global role [{$role->name}] holds a non-admin permission."
            );
        }
    }

    public function test_admin_role_store_rejects_non_admin_permissions(): void
    {
        $admin = $this->makeAdmin();
        $admin->assignRole('Super Admin');

        $assistantPermissionId = Permission::where('type', PermissionsTypeEnum::ASSISTANT->value)->value('id');

        $this->actingAs($admin)
            ->post(route('web-admin.roles.store'), [
                'name' => 'Sneaky Role',
                'permissions' => [$assistantPermissionId],
            ])
            ->assertSessionHasErrors('permissions.0');

        $this->assertDatabaseMissing('roles', ['name' => 'Sneaky Role']);
    }

    public function test_clinic_role_store_ignores_admin_permissions(): void
    {
        [$doctorUser, $doctor] = $this->makeDoctor();

        $adminPermissionId = Permission::where('type', PermissionsTypeEnum::ADMIN->value)->value('id');
        $clinicPermissionId = Permission::where('type', PermissionsTypeEnum::ASSISTANT->value)->value('id');

        $role = ClinicStoreRole::execute($doctorUser, 'Clinic Role', [$adminPermissionId, $clinicPermissionId]);

        $this->assertEquals($doctor->id, $role->doctor_id);
        $this->assertEquals([$clinicPermissionId], $role->permissions->pluck('id')->all());
    }

    public function test_admin_role_service_rejects_a_clinic_role(): void
    {
        [$doctorUser] = $this->makeDoctor();

        $clinicRole = ClinicStoreRole::execute($doctorUser, 'Clinic Role', []);

        try {
            AdminDeleteRole::execute($clinicRole);
            $this->fail('Expected a 403 for a clinic role passed to an admin role service.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('roles', ['id' => $clinicRole->id]);
    }

    public function test_clinic_role_service_rejects_an_admin_role(): void
    {
        [$doctorUser] = $this->makeDoctor();

        $adminRole = Role::create([
            'name' => 'Global Role',
            'guard_name' => 'web',
            'doctor_id' => null,
        ]);

        try {
            ClinicDeleteRole::execute($doctorUser, $adminRole);
            $this->fail('Expected a 403 for an admin role passed to a clinic role service.');
        } catch (HttpException $e) {
            $this->assertSame(403, $e->getStatusCode());
        }

        $this->assertDatabaseHas('roles', ['id' => $adminRole->id]);
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin',
            'email' => uniqid('admin').'@example.com',
            'phone' => '1'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => UserType::ADMIN,
        ]);
    }

    private function makeDoctor(): array
    {
        $user = User::create([
            'name' => 'Doctor',
            'email' => uniqid('doctor').'@example.com',
            'phone' => '2'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => UserType::DOCTOR,
        ]);

        $doctor = Doctor::create(['user_id' => $user->id]);

        return [$user, $doctor];
    }
}
