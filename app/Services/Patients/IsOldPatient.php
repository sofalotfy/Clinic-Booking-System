<?php

namespace App\Services\Patients;

use App\Models\Appointments;

class IsOldPatient
{
    public static function execute(
        int $doctorId,
        int $patientId,
    ) {
        return Appointments::where([
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
        ])->exists();
    }
}