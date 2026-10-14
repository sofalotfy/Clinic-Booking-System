<?php

namespace App\AdminServices\AdminPermissions;

use App\Enums\AdminPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Services\Permissions\ListPermissions as ListPermissionsService;

class ListPermissions
{
    public static function execute($request)
    {
        return ListPermissionsService::execute($request->user(), ['type' => PermissionsTypeEnum::ADMIN->value])
            ->get()
            ->map(fn ($permission) => [
                'id' => $permission->id,
                'name' => $permission->name,
                'group' => AdminPermissionsEnum::tryFrom($permission->name)?->group() ?? 'Other',
                'authorized' => (int) $permission->authorized,
            ])
            ->values();
    }
}
