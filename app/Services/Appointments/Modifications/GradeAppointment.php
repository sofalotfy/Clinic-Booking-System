<?php

namespace App\Services\Appointments\Modifications;


class GradeAppointment
{
    public static function execute($user, $appointment, $grade)
    {
        if($appointment->grade == $grade)
            return $appointment;
            
        $appointment->update(
            [
                'grade'  =>  $grade??$appointment->grade,
            ]
        );
        
        return $appointment;
    }
}