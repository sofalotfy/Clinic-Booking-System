<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;

class FinnishAppointment
{
    public static function execute($user, $appointment)
    {
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::DONE,
            ]
        );

        return $appointment;
    }
}