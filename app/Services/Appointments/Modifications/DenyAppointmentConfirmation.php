<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;

class DenyAppointmentConfirmation
{
    public static function execute($user, $appointment)
    {
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::CANCEL,
            ]
        );
    }
}