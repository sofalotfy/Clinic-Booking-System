<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\AssistantPermissionsEnum;
use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\NotificationEnum;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $assistantPermissions = AssistantPermissionsEnum::cases();
        foreach ($assistantPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web',
                'type'  => PermissionsTypeEnum::ASSISTANT]
            );
        }
        $adminPermissions = AdminPermissionsEnum::cases();
        foreach ($adminPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web',
                'type'  => PermissionsTypeEnum::ADMIN]
            );
        }

        $notificationPermissions = array_filter(
            array_map(fn(NotificationEnum $case) => $case->permission(), NotificationEnum::cases())
        );
        
        foreach ($notificationPermissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web',
                'type'  => PermissionsTypeEnum::NOTIFICATION]
            );
        }

        // doctor_id is pinned to null so firstOrCreate cannot latch onto a
        // doctor-scoped role of the same name created through the API, whose
        // permissions would then be overwritten by syncPermissions.
        $superAdminRole = Role::firstOrCreate([
            'name'       => 'Super Admin',
            'doctor_id'  => null,
            'guard_name' => 'web',
        ]);
        $superAdminRole->syncPermissions(Permission::pluck('id'));

        // syncPermissions reconciles the pivot on every run, so permissions
        // added to the enums later are picked up without extra bookkeeping.
        $doctorPermissionIds = Permission::where('type', '!=', PermissionsTypeEnum::ADMIN->value)->pluck('id');

        // The doctor role is scoped per doctor rather than global because
        // GetClinicPriviligedUsers looks roles up by doctor_id. A single
        // doctor_id => null role is invisible to it, so clinic notifications
        // would resolve no receivers at all.
        $doctors = User::query()
            ->has('doctor')
            ->with('doctor')
            ->chunkById(200, function ($users) use ($doctorPermissionIds) {
                foreach ($users as $user) {
                    $role = Role::firstOrCreate([
                        'name'       => 'doctor',
                        'doctor_id'  => $user->doctor?->id,
                        'guard_name' => 'web',
                    ]);

                    $role->syncPermissions($doctorPermissionIds);

                    if (! $user->roles()->whereKey($role->id)->exists()) {
                        $user->assignRole($role);
                    }
                }
            });

        // Legacy global role from before the role became doctor-scoped. It
        // grants nothing GetClinicPriviligedUsers can find, so drop the
        // assignments and the role itself.
        $legacyRoles = Role::whereNull('doctor_id')->where('name', 'doctor')->get();

        foreach ($legacyRoles as $legacyRole) {
            User::role($legacyRole)->chunkById(200, function ($users) use ($legacyRole) {
                $users->each(fn ($user) => $user->removeRole($legacyRole));
            });

            $legacyRole->delete();
        }
    }
}
