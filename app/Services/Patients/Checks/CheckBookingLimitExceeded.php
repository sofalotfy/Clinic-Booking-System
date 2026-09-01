<?php

namespace App\Services\Patients\Checks;

use App\Models\Appointment;
use App\Enums\AppointmentStatus;

class CheckBookingLimitExceeded
{
    public static function execute(
        int $doctorId,
        int $patientId,
    ) {
        return Appointment::where([
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
        ])
        ->whereNotIn('status', AppointmentStatus::working())
        ->count() >= 1;
    }
}