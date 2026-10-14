<?php

namespace App\AdminServices\AdminRoles;

use App\Models\Role;
use App\Services\AdminRoles\ShowRole as ShowRoleService;

class ShowRole
{
    public static function execute(Role $role)
    {
        $role = ShowRoleService::execute($role);

        return [
            'id' => $role->id,
            'name' => $role->name,
            'protected' => $role->name === 'Super Admin',
            'permission_ids' => $role->permissions->pluck('id')->values()->all(),
            'permissions' => $role->permissions->map(fn ($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
            ])->values(),
        ];
    }
}
