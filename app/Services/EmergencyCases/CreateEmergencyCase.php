<?php

namespace App\Services\EmergencyCases;

use App\Models\EmergencyCase;
use App\Models\Doctor;
use App\Services\Notifications\Doctor\UrgentNotify;
use App\Enums\NotificationsType;

class CreateEmergencyCase
{
    public static function execute(
        int $patientId,
        int $doctorId,
        string $symptoms,
    ): EmergencyCase {
        $emergencyCase = EmergencyCase::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'symptoms' => $symptoms,
        ]);

        UrgentNotify::execute(  
            Doctor::find($doctorId),
            NotificationsType::EMERGENCY,
            'Emergency Case',
            "Patient {$emergencyCase->patient->user->name} has reported an emergency. Please check on them as soon as possible.",
        );

        return $emergencyCase;
    }

    
}