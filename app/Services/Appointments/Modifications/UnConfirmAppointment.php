<?php

namespace App\Services\Appointments\Modifications;

class UnConfirmAppointment
{
    public static function execute($user, $appointment)
    {
        if (!$appointment->isConfirmed)
            return $appointment;

        $appointment->update(
            [
                'isConfirmed'  =>  false,
            ]
        );
    }
}