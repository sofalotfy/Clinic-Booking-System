<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Enums\UserType;
use App\Services\Appointments\General\NotifyPatientOfReschedule;

class ActivateAppointment
{
    public static function execute($user, $appointment)
    {
        if ($appointment->status == AppointmentStatus::ACTIVE)
            return $appointment;

        $appointment->update([
            'status' => AppointmentStatus::ACTIVE,
            'isConfirmed' => false,
        ]);

        NotifyPatientOfReschedule::execute($user, $appointment, $appointment->date, AppointmentUpdateNotificationTypes::ACTIVATE);

        return $appointment;
    }
}