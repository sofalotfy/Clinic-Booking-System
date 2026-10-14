<?php

namespace App\APIServices\Roles;

use App\Enums\PermissionsTypeEnum;
use App\Models\Role;
use App\Services\Roles\UpdateRole as UpdateService;
use Illuminate\Validation\Rule;

class UpdateRole
{
    public static function execute($request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => [
                'integer',
                Rule::exists('permissions', 'id')->where('type', '!=', PermissionsTypeEnum::ADMIN->value),
            ],
        ]);

        $role = UpdateService::execute($request->user(), $role, $validated['name'], $validated['permissions']);

        return ShowRole::execute($request, $role);
    }
}