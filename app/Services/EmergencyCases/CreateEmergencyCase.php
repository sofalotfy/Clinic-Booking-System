<?php

namespace App\Services\EmergencyCases;

use App\Models\EmergencyCase;

class CreateEmergencyCase
{
    public static function execute(
        int $patientId,
        int $doctorId,
        string $symptoms,
    ): EmergencyCase {
        return EmergencyCase::create([
            'patient_id' => $patientId,
            'doctor_id' => $doctorId,
            'symptoms' => $symptoms,
        ]);
    }
}