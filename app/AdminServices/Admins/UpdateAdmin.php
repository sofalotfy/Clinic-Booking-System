<?php

namespace App\AdminServices\Admins;

use App\Enums\Gender;
use App\Models\User;
use App\Services\Admins\UpdateAdmin as UpdateAdminService;
use Illuminate\Validation\Rule;

class UpdateAdmin
{
    public static function execute($request, User $admin)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => 'nullable|email|unique:users,email,'.$admin->id,
            'phone' => 'nullable|string|max:20|unique:users,phone,'.$admin->id,
            'password' => 'nullable|string|min:8',
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

        $admin = UpdateAdminService::execute($admin, $validated);

        if ($request->user()->id !== $admin->id) {
            $admin->syncRoles(array_map('intval', $validated['roles'] ?? []));
        }

        return $admin;
    }
}
