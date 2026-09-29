<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;

class ActivateAppointment
{
    public static function execute($user, $appointment)
    {
        if ($appointment->status == AppointmentStatus::ACTIVE)
            return $appointment;

        $appointment->update([
            'status' => AppointmentStatus::ACTIVE,
            'isConfirmed' => false,
        ]);

        return $appointment;
    }
}
