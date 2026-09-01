<?php

namespace App\Services\TemplatePlans\Checks;

use App\Models\Day;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;

class CheckSlotAvailability
{
    public static function execute($day, $time)
    {
        $requestedDateTime = $day->date . ' ' . $time;

        return !Appointment::where('date', $requestedDateTime)
            ->where('doctor_id', $day->doctor_id)
            ->whereNotIn('status', AppointmentStatus::working())
            ->exists();
    }
}