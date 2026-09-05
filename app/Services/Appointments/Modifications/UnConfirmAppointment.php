<?php

namespace App\Services\Appointments\Modifications;

class UnConfirmAppointment
{
    public static function execute($user, $appointment)
    {
        $appointment->update(
            [
                'isConfirmed'  =>  false,
            ]
        );
    }
}