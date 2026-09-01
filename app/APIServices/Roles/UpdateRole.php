<?php

namespace App\APIServices\Roles;

use Spatie\Permission\Models\Role;
use App\Services\Roles\UpdateRole as UpdateService;

class UpdateRole
{
    public static function execute($request, Role $role)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = UpdateService::execute($request->user(), $role, $validated['name'], $validated['permissions']);

        return ShowRole::execute($request, $role);
    }
}