<?php

namespace App\AdminServices\AdminRoles;

use App\Services\AdminRoles\ListRoles as ListRolesService;

class ListRoles
{
    public static function execute($request)
    {
        return ListRolesService::execute()
            ->paginate(15)
            ->through(fn ($role) => self::format($role));
    }

    public static function options()
    {
        return ListRolesService::execute()
            ->get()
            ->map(fn ($role) => [
                'id' => $role->id,
                'name' => $role->name,
            ])
            ->values();
    }

    private static function format($role)
    {
        return [
            'id' => $role->id,
            'name' => $role->name,
            'permissions' => $role->permissions->pluck('name')->values(),
            'users_count' => $role->users_count,
            'protected' => $role->name === 'Super Admin',
        ];
    }
}
