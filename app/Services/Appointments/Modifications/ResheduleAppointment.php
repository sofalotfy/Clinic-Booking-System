<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Services\Appointments\General\NotifyPatientOfReschedule;

class ResheduleAppointment
{
    public static function execute($user, $appointment, $new_date, $duration = null,  $cause = AppointmentUpdateNotificationTypes::COLIDE)
    {
        $appointment->update(
            [
                'date' => $new_date,
                'duration' => $duration ?? $appointment->duration,
                'status'  =>  AppointmentStatus::ACTIVE,
                'isConfirmed' => false,
            ]
        );

        if($user->isPatient())
            return $appointment;

        NotifyPatientOfReschedule::execute($user, $appointment, $new_date, $cause);

        return $appointment;
    }
}