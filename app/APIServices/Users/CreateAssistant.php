<?php

namespace App\APIServices\Users;

use App\Models\Assistant;
use App\Models\User;
use App\Enums\UserType;

class CreateAssistant
{
    public static function execute($request, $doctor)
    {
        $user = User::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'password' => bcrypt($request['password']),
            'phone' => $request['phone'],
            'type' => UserType::ASSISTANT->value,
        ]);

        $assistant = Assistant::create([
            'user_id' => $user->id,
            'doctor_id' => $doctor->id,
        ]);

        return $assistant->with('User')->first();
    }
}
