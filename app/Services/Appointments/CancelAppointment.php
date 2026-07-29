<?php

namespace App\Services\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Enums\AppointmentUpdateNotificationTypes;

class CancelAppointment
{
    public static function execute($appointment, $type = AppointmentUpdateNotificationTypes::CANCEL)
    {
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::CANCELLED,
            ]
        );

        NotifyReshedule::execute($appointment, null, $type);

        return $appointment;
    }
}