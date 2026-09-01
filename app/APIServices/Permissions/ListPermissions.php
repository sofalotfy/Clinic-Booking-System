<?php

namespace App\APIServices\Permissions;

use App\Services\Permissions\ListPermissions as ListService;

class ListPermissions
{
    public static function execute($request)
    {
        $permissions = ListService::execute($request->user(), $request->all())
            ->select(self::getSelects())
            ->get();

        return response()->json([
            'permissions' => $permissions,
        ]);
    }

    private static function getSelects()
    {
        return [
            'id',
            'name',
            'type',
        ];
    }
}