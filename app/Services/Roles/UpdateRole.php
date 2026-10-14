<?php

namespace App\Services\Roles;

use App\Models\User;
use App\Models\Role;

class UpdateRole
{
    public static function execute(User $user, Role $role, string $roleName, array $permissions)
    {
        abort_unless($role->isClinic(), 403, 'This is not a clinic role.');

        $role->update([
            'name' => $roleName,
        ]);

        $role->syncPermissions(StoreRole::clinicPermissionIds($permissions ?? []));

        return $role;
    }
}
