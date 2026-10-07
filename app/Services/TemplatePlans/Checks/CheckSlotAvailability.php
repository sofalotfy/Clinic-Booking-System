<?php

namespace App\Services\TemplatePlans\Checks;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;

class CheckSlotAvailability
{
    public static function execute($day, $time)
    {
        $requestedDateTime = $day->date.' '.$time;

        return ! Appointment::where('date', $requestedDateTime)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', AppointmentStatus::working())
            ->exists() && $day->isActive();
    }
}
