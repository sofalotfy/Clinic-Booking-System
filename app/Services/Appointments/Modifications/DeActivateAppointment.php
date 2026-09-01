<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;

class DeActivateAppointment
{
    public static function execute($user, $appointment)
    {
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::PENDING,
            ]
        );

        return $appointment;
    }
}