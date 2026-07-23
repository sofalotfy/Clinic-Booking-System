<?php

namespace App\Services\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use Carbon\Carbon;


class QueueAppointment
{
    public static function execute($appointment, $duration = null, $type = 'overflow')
    {   
        $appointment->update(
            [
                'duration' => $duration ?? $appointment->duration,
                'status'  =>  AppointmentStatus::QUEUED,
                'isConfirmed' => false,
            ]
        );

        NotifyReshedule::execute($appointment, null, $type);

        return $appointment;
    }
}