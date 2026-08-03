<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;
use Illuminate\Validation\Rule;

class UpdateAssistant
{
    public static function execute(Assistant $assistant, $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($assistant->user_id),
            ],
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->ignore($assistant->user_id),
            ],
            'password' => 'nullable|string|min:8',
        ]);

        $assistant->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        if (!empty($validated['password'])) {
            $assistant->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }
        return $assistant;
    }
}