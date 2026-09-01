<?php

namespace App\APIServices\Users;

use Illuminate\Validation\Rule;
use App\Enums\Gender;
use App\Models\User;

class UpdateProfile
{
    public static function execute($request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users', 'email')->ignore($user->id),
            ],
            'phone' => 'nullable|string|max:15',
            'age' => 'nullable|integer|min:0|max:150',
            'gender' => ['nullable', Rule::enum(Gender::class)],
            'area' => 'nullable|string',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        if ($request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');

            $validated['image'] = $path;
        }

        $validated = array_filter($validated, function ($value) {
            return !is_null($value);
        });

        $user->fill($validated);
        $user->save();

        return ShowUser::execute($request, $user);
    }
}
