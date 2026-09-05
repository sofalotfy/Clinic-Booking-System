<?php

namespace App\Services\Appointments\Modifications;

class ConfirmAppointment
{
    public static function execute($user, $appointment)
    {
        $appointment->update(
            [
                'isConfirmed'  =>  true,
            ]
        );
    }
}