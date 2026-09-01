<?php

namespace App\Services\Clinics;

use App\Models\User;
use App\Enums\UserType;

class GetClinicDoctor
{
    public static function execute(User $user)
    {
        return match ($user->type) {
            UserType::DOCTOR => $user->doctor,
            UserType::ASSISTANT => $user->assistant->doctor,
            default => null,
        };
    }
}