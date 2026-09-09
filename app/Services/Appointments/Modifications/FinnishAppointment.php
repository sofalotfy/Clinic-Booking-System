<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;

class FinnishAppointment
{
    public static function execute($user, $appointment)
    {
        if ($appointment->status == AppointmentStatus::DONE)
            return $appointment;
        
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::DONE,
            ]
        );

        return $appointment;
    }
}