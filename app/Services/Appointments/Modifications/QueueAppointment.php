<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;

class QueueAppointment
{
    public static function execute($user, $appointment, $duration = null)
    {
        if ($appointment->status == AppointmentStatus::QUEUED) {
            return $appointment;
        }

        $appointment->update(
            [
                'duration' => $duration ?? $appointment->duration,
                'status' => AppointmentStatus::QUEUED,
                'isConfirmed' => false,
            ]
        );

        if ($user->isPatient()) {
            return $appointment;
        }

        return $appointment;
    }
}
