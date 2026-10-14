<?php

namespace App\AdminServices\AdminRoles;

use App\Enums\PermissionsTypeEnum;
use App\Services\AdminRoles\StoreRole as StoreRoleService;
use Illuminate\Validation\Rule;

class StoreRole
{
    public static function execute($request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->whereNull('doctor_id')],
            'permissions' => 'array',
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('type', PermissionsTypeEnum::ADMIN->value)],
        ]);

        return StoreRoleService::execute($validated['name'], $validated['permissions'] ?? []);
    }
}
