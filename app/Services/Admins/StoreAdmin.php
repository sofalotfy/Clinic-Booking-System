<?php

namespace App\Services\Admins;

use App\Enums\UserType;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class StoreAdmin
{
    public static function execute(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'type' => UserType::ADMIN,
            'age' => $data['age'] ?? null,
            'gender' => $data['gender'] ?? null,
            'area' => $data['area'] ?? null,
            'image' => $data['image'] ?? null,
        ]);

        return ShowAdmin::execute($user);
    }
}
