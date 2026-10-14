<?php

namespace App\Services\AdminRoles;

use App\Models\Role;

class UpdateRole
{
    public static function execute(Role $role, string $name, array $permissionIds)
    {
        abort_unless($role->isAdmin(), 403, 'This is not an admin role.');

        $role->update([
            'name' => $name,
        ]);

        $role->syncPermissions(array_map('intval', $permissionIds));

        return ShowRole::execute($role);
    }
}
