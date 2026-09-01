<?php

namespace App\APIServices\Permissions;

use App\Services\Permissions\GetUserPermissions as GetUserPermissionsService;

class GetUserPermissions
{
    public static function execute($request)
    {
        $roles = GetUserPermissionsService::execute($request->user(), $request->user()->clinicDoctor())
            ->select(self::getSelects())
            ->get();

        return response()->json([
            'roles' => $roles,
        ]);
    }

    private static function getSelects()
    {
        return [
            'id',
            'name',
        ];
    }
}