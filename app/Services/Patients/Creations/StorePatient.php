<?php

namespace App\Services\Patients\Creations;

use App\Models\Patient;
use App\Models\User;
use App\Enums\UserType;

class StorePatient
{
    public static function execute($phone)
    {
        $user = User::create([
            'phone' => $phone,
            'type' => UserType::PATIENT,
            // Leave other fields null for now
        ]);

        $patient = Patient::create([
            'user_id' => $user->id,
        ]);

        return $patient;
    }
}