<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

class StoreAssistant
{
    public static function execute($request)
    {
        $doctorId = $request->user()->type == UserType::DOCTOR
            ? $request->user()->doctor->id
            : $request->user()->assistant->doctor_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'type' => UserType::ASSISTANT,
        ]);

        $assistant = Assistant::create([
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
        ]);
        return $assistant;
    }
}