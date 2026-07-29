<?php

namespace App\Services\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;
use App\Enums\AppointmentUpdateNotificationTypes;

class ResheduleAppointment
{
    public static function execute($appointment, $new_date, $duration)
    {   
        $appointment->update(
            [
                'date' => $new_date,
                'duration' => $duration,
                'status'  =>  AppointmentStatus::ACTIVE,
                'isConfirmed' => false,
            ]
        );

        NotifyReshedule::execute($appointment, $new_date, AppointmentUpdateNotificationTypes::COLIDE);

        return $appointment;
    }
}