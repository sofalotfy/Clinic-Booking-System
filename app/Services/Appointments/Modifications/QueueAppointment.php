<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Services\Appointments\General\NotifyPatientOfReschedule;

class QueueAppointment
{
    public static function execute($user, $appointment, $duration = null, $type = AppointmentUpdateNotificationTypes::OVERFLOW)
    {   
        $appointment->update(
            [
                'duration' => $duration ?? $appointment->duration,
                'status'  =>  AppointmentStatus::QUEUED,
                'isConfirmed' => false,
            ]
        );

        if($user->isPatient())
            return $appointment;

        NotifyPatientOfReschedule::execute($user, $appointment, null, $type);

        return $appointment;
    }
}