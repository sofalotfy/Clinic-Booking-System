<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\AssistantPermissionsEnum;
use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\NotificationEnum;
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

        $notificationPermissions = array_map(
            fn(NotificationEnum $case) => $case->permission(),
            NotificationEnum::cases()
        );

        // if you want to drop the nulls (cases with no permission mapped):
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

        $role = Role::firstOrCreate([
            'name' => 'Super Admin'
        ]);
        $role->syncPermissions(Permission::pluck('id'));
    }
}
