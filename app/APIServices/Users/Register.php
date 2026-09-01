<?php

namespace App\APIServices\Users;

use App\Models\User;
use App\Models\Doctor;
use App\Models\Patient;
use Illuminate\Validation\Rule;
use App\Enums\UserType;

class Register
{
    public static function execute($request)
    {
        // 1. Validate input
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:8|confirmed',
            'phone' => 'required|string|max:15|unique:users,phone',
            'type' => ['nullable', Rule::enum(UserType::class)],
        ]);

        // 2. Create new user
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
            'phone' => $request->phone,
            'type' => $request->type,
        ]);

        // 3. Create the related profile
        if (empty($validated['type']) || $validated['type'] == UserType::PATIENT->value) {
            Patient::create([
                'user_id' => $user->id,
            ]);
        } elseif ($validated['type'] == UserType::DOCTOR->value) {
            Doctor::create([
                'user_id' => $user->id,
            ]);
        }

        $response = ShowUser::execute($request, $user);
        $data = $response->getData(true);
        $data['token'] = $user->createToken('api-token')->plainTextToken;
        $response->setData($data);
        return $response;
    }
}
