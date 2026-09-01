<?php

namespace App\APIServices\Permissions;

use App\Services\Permissions\ListPermissions as ListService;
use App\Enums\PermissionsTypeEnum;

class ListAssistantPermissions
{
    public static function execute($request)
    {
        $permissions = ListService::execute($request->user(), ["type" => PermissionsTypeEnum::ASSISTANT->value])
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
        ];
    }
}