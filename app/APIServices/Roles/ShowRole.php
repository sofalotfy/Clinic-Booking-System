<?php

namespace App\APIServices\Roles;

use App\Services\Roles\ShowRoles as ShowService;
use Spatie\Permission\Models\Role;

class ShowRole
{
    public static function execute($request, Role $role)
    {
        $role = ShowService::execute($request->user(), $role)
            ->select(self::getSelects())
            ->get();

        return response()->json([
            'role' => self::format($role),
        ]);
    }

    private static function getSelects()
    {
        return [
            'roles.id',
            'roles.name',
            'permissions.id as permission_id',
            'permissions.name as permission_name',
            'permissions.type as permission_type',
        ];
    }

    private static function format($role)
    {
        $first = $role->first();

        return [
            'id' => $first->id,
            'name' => $first->name,
            'permissions' => $role
                ->whereNotNull('permission_id')
                ->map(fn ($permission) => [
                    'id' => $permission->permission_id,
                    'name' => $permission->permission_name,
                    'type' => $permission->permission_type,
                ])
                ->values(),
        ];
    }
}