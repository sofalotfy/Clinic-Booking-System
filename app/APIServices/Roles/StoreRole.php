<?php

namespace App\APIServices\Roles;

use App\Services\Roles\StoreRole as StoreService;
use Spatie\Permission\Models\Role;

class StoreRole
{
    public static function execute($request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = StoreService::execute($request->user(), $validated['name'], $validated['permissions'] ?? []);

        return ShowRole::execute($request, $role);
    }
}