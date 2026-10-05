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
        $doctorRole = Role::firstOrCreate([
            'name'       => 'doctor',
            'doctor_id'  => null,
            'guard_name' => 'web',
        ]);
        $doctorRole->syncPermissions(
            Permission::where('type', '!=', PermissionsTypeEnum::ADMIN->value)->pluck('id')
        );

        $missingDoctors = fn () => User::query()
            ->has('doctor')
            ->whereDoesntHave('roles', fn ($query) => $query->where('roles.id', $doctorRole->id));

        $missing = $missingDoctors()->count();

        if ($missing === 0) {
            $this->command?->info('All doctors already have the doctor role.');

            return;
        }

        $missingDoctors()->chunkById(200, function ($users) use ($doctorRole) {
            foreach ($users as $user) {
                $user->assignRole($doctorRole);
            }
        });

        $this->command?->info("Assigned the doctor role to {$missing} doctor(s).");
    }
}
