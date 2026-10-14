<?php

namespace App\Services\AdminRoles;

use App\Models\Role;

class StoreRole
{
    public static function execute(string $name, array $permissionIds)
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'doctor_id' => null,
        ]);

        $role->syncPermissions(array_map('intval', $permissionIds));

        return ShowRole::execute($role);
    }
}
