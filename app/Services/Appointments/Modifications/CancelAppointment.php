<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Services\Appointments\General\NotifyPatientOfReschedule;

class CancelAppointment
{
    public static function execute($user, $appointment, $type = AppointmentUpdateNotificationTypes::CANCEL)
    {
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::CANCELLED,
            ]
        );

        if($user->isPatient())
            return $appointment;

        NotifyPatientOfReschedule::execute($user, $appointment, null, $type);

        return $appointment;
    }
}