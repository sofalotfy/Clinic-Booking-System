<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\AssistantPermissionsEnum;
use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
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
        $permissions = AssistantPermissionsEnum::cases();
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web',
                'type'  => PermissionsTypeEnum::ASSISTANT]
            );
        }
        $permissions = AdminPermissionsEnum::cases();
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web',
                'type'  => PermissionsTypeEnum::ADMIN]
            );
        }
        $role = Role::firstOrCreate([
            'name' => 'Super Admin'
        ]);
        $role->syncPermissions(Permission::pluck('id'));
    }
}
