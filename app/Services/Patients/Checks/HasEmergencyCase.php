<?php

namespace App\Services\Patients\Checks;

use App\Models\EmergencyCase;


class HasEmergencyCase
{
    public static function execute(int $patientId, int $doctorId): bool
    {
        return EmergencyCase::where('patient_id', $patientId)
            ->where('doctor_id', $doctorId)
            ->exists();
    }
}