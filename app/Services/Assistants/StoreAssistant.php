<?php

namespace App\Services\Assistants;

use App\Models\Assistant;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use App\Enums\UserType;

class StoreAssistant
{
    public static function execute($doctorId, $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'password' => Hash::make($data['password']),
            'type' => UserType::ASSISTANT,
        ]);

        $assistant = Assistant::create([
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
        ]);

        return ShowAssistant::execute($assistant);
    }
}