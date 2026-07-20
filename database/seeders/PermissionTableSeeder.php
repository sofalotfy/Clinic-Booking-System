<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Enums\PermissionsEnum;
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
        $permissions = PermissionsEnum::cases();
        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['name' => $permission],
                ['guard_name' => 'web']
            );
        }
        $role = Role::firstOrCreate([
            'name' => 'Super Admin'
        ]);
        $role->syncPermissions(Permission::pluck('id'));
    }
}
