<?php

namespace App\APIServices\Roles;

use App\Services\Roles\ListRoles as ListService;

class ListRoles
{
    public static function execute($request)
    {
        $roles = ListService::execute($request->user())
            ->select(self::getSelects())
            ->get();

        return response()->json([
            'roles' => self::format($roles),
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

    private static function format($plans)
{
    return $plans->groupBy('roles.id')->map(function ($plan) {
        return [
            'id' => $plan->first()->id,
            'name' => $plan->first()->name,
            'permissions' => $plan
                ->whereNotNull('permission_id')
                ->map(fn ($permission) => [
                    'id' => $permission->permission_id,
                    'name' => $permission->permission_name,
                    'type' => $permission->permission_type,
                ])
                ->values(),
        ];
    })->values();
}
}