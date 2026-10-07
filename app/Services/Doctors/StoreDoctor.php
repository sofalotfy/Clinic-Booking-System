<?php

namespace App\Services\Doctors;

use App\Enums\UserType;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StoreDoctor
{
    public static function execute(array $data, $request = null)
    {
        if ($request && $request->hasFile('image')) {
            $path = $request->file('image')->store('users', 'public');
            $data['image'] = $path;
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'type' => UserType::DOCTOR,
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'area' => $data['area'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        $doctor = Doctor::create([
            'user_id' => $user->id,
            'description' => $data['description'] ?? null,
        ]);

        return ShowDoctor::execute($doctor);
    }
}
