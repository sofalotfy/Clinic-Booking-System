<?php

namespace App\Services\Roles;

use App\Models\User;
use Spatie\Permission\Models\Role;

class UpdateRole
{
    public static function execute(User $user, Role $role, string $roleName, array $permissions)
    {
        $role->update([
            'name' => $roleName,
        ]);

        $role->syncPermissions($permissions ?? []);

        return $role;
    }
}