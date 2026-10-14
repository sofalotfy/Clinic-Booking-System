<?php

namespace App\AdminServices\AdminRoles;

use App\Enums\PermissionsTypeEnum;
use App\Models\Role;
use App\Services\AdminRoles\UpdateRole as UpdateRoleService;
use Illuminate\Validation\Rule;

class UpdateRole
{
    public static function execute($request, Role $role)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')->whereNull('doctor_id')->ignore($role->id)],
            'permissions' => 'array',
            'permissions.*' => ['integer', Rule::exists('permissions', 'id')->where('type', PermissionsTypeEnum::ADMIN->value)],
        ]);

        return UpdateRoleService::execute($role, $validated['name'], $validated['permissions'] ?? []);
    }
}
