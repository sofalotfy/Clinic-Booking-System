<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;
use App\Enums\UserType;

class ListAssistants
{
    public static function execute($user)
    {
        $doctorId = $user->type == UserType::DOCTOR
            ? $user->doctor->id
            : $user->assistant->doctor_id;

        $assistants = Assistant::where('doctor_id', $doctorId)
            ->with([
                'user.roles:id,name',
                'user:id,name,email,phone'
            ])
            ->get();

        return $assistants;
    }
}