<?php

namespace Tests\Feature;

use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Doctor;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AdminPermissionsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);
    }

    public function test_super_admin_can_access_every_admin_section(): void
    {
        $admin = $this->makeAdmin();
        $admin->assignRole('Super Admin');

        foreach ([
            route('web-admin.dashboard'),
            route('web-admin.doctors.index'),
            route('web-admin.admins.index'),
            route('web-admin.roles.index'),
            route('web-admin.permissions.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertOk();
        }
    }

    public function test_admin_without_roles_is_forbidden_from_every_admin_section(): void
    {
        $admin = $this->makeAdmin();

        foreach ([
            route('web-admin.doctors.index'),
            route('web-admin.admins.index'),
            route('web-admin.roles.index'),
            route('web-admin.permissions.index'),
        ] as $url) {
            $this->actingAs($admin)->get($url)->assertForbidden();
        }
    }

    public function test_admin_with_view_doctors_permission_can_view_doctors_but_nothing_else(): void
    {
        $doctor = $this->makeDoctor();

        $role = $this->makeGlobalRole('Doctors Viewer', [
            [AdminPermissionsEnum::VIEW_DOCTORS->value, PermissionsTypeEnum::ADMIN],
        ]);

        $admin = $this->makeAdmin();
        $admin->assignRole($role);

        $this->actingAs($admin)->get(route('web-admin.doctors.index'))->assertOk();
        $this->actingAs($admin)->get(route('web-admin.admins.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('web-admin.roles.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('web-admin.permissions.index'))->assertForbidden();
        $this->actingAs($admin)->get(route('web-admin.doctors.edit', $doctor->id))->assertForbidden();
    }

    public function test_same_name_assistant_permission_does_not_grant_admin_permission(): void
    {
        $doctor = $this->makeDoctor();

        $role = $this->makeGlobalRole('Assistant Update Doctor', [
            [AdminPermissionsEnum::UPDATE_DOCTOR->value, PermissionsTypeEnum::ASSISTANT],
        ]);

        $admin = $this->makeAdmin();
        $admin->assignRole($role);

        $assistantPermission = Permission::query()
            ->where('name', AdminPermissionsEnum::UPDATE_DOCTOR->value)
            ->where('type', PermissionsTypeEnum::ASSISTANT)
            ->firstOrFail();

        $this->assertTrue($admin->hasPermissionTo($assistantPermission));

        $this->actingAs($admin)
            ->get(route('web-admin.doctors.edit', $doctor->id))
            ->assertForbidden();
    }

    public function test_admin_cannot_delete_himself(): void
    {
        $admin = $this->makeAdmin();
        $admin->assignRole('Super Admin');

        $this->actingAs($admin)
            ->delete(route('web-admin.admins.destroy', $admin->id))
            ->assertRedirect(route('web-admin.admins.index'))
            ->assertSessionHasErrors('admin');

        $this->assertDatabaseHas('users', ['id' => $admin->id]);
    }

    public function test_self_delete_button_is_hidden_for_the_current_admin(): void
    {
        $admin = $this->makeAdmin();
        $admin->assignRole('Super Admin');

        $other = $this->makeAdmin();

        $response = $this->actingAs($admin)->get(route('web-admin.admins.index'));

        $response->assertOk();
        $response->assertDontSee('action="'.route('web-admin.admins.destroy', $admin->id).'"', false);
        $response->assertSee('action="'.route('web-admin.admins.destroy', $other->id).'"', false);
    }

    public function test_action_buttons_are_hidden_without_permission(): void
    {
        $role = $this->makeGlobalRole('Doctors Viewer', [
            [AdminPermissionsEnum::VIEW_DOCTORS->value, PermissionsTypeEnum::ADMIN],
        ]);

        $admin = $this->makeAdmin();
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get(route('web-admin.doctors.index'))
            ->assertOk()
            ->assertDontSee('Add Doctor')
            ->assertDontSee('View')
            ->assertDontSee('Edit')
            ->assertDontSee('Delete');
    }

    public function test_header_tabs_reflect_permissions(): void
    {
        $role = $this->makeGlobalRole('Doctors Viewer', [
            [AdminPermissionsEnum::VIEW_DOCTORS->value, PermissionsTypeEnum::ADMIN],
        ]);

        $admin = $this->makeAdmin();
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get(route('web-admin.doctors.index'))
            ->assertOk()
            ->assertSee(route('web-admin.doctors.index'))
            ->assertDontSee(route('web-admin.admins.index'))
            ->assertDontSee(route('web-admin.roles.index'))
            ->assertDontSee(route('web-admin.permissions.index'));
    }

    private function makeAdmin(): User
    {
        return User::create([
            'name' => 'Admin '.uniqid(),
            'email' => uniqid('admin').'@example.com',
            'phone' => '1'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => UserType::ADMIN,
        ]);
    }

    private function makeDoctor(): Doctor
    {
        $user = User::create([
            'name' => 'Doctor',
            'email' => uniqid('doctor').'@example.com',
            'phone' => '2'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => UserType::DOCTOR,
        ]);

        return Doctor::create(['user_id' => $user->id]);
    }

    private function makeGlobalRole(string $name, array $permissions): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'doctor_id' => null,
        ]);

        foreach ($permissions as [$permissionName, $type]) {
            $permission = Permission::query()
                ->where('name', $permissionName)
                ->where('type', $type)
                ->firstOrFail();

            $role->givePermissionTo($permission);
        }

        return $role;
    }
}
