<?php

namespace App\AdminServices\Admins;

use App\Enums\Gender;
use App\Services\Admins\StoreAdmin as StoreAdminService;
use Illuminate\Validation\Rule;

class StoreAdmin
{
    public static function execute($request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'roles' => 'array',
            'roles.*' => ['integer', Rule::exists('roles', 'id')->whereNull('doctor_id')],
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('users', 'public');
        }

        $admin = StoreAdminService::execute($validated);

        $admin->syncRoles(array_map('intval', $validated['roles'] ?? []));

        return $admin;
    }
}
