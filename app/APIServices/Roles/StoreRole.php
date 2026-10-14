<?php

namespace App\APIServices\Roles;

use App\Enums\PermissionsTypeEnum;
use App\Services\Roles\StoreRole as StoreService;
use Illuminate\Validation\Rule;

class StoreRole
{
    public static function execute($request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where('type', '!=', PermissionsTypeEnum::ADMIN->value),
            ],
        ]);

        $role = StoreService::execute($request->user(), $validated['name'], $validated['permissions'] ?? []);

        return ShowRole::execute($request, $role);
    }
}