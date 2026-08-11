<?php

namespace App\Services\Patients;

use App\Models\Appointment;

class IsOldPatient
{
    public static function execute(
        int $doctorId,
        int $patientId,
    ) {
        return Appointment::where([
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
        ])->exists();
    }
}