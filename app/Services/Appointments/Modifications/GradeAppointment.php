<?php

namespace App\Services\Appointments\Modifications;


class GradeAppointment
{
    public static function execute($user, $appointment, $grade)
    {
        $appointment->update(
            [
                'grade'  =>  $grade??$appointment->grade,
            ]
        );
        
        return $appointment;
    }
}