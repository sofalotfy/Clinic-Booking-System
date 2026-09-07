<?php

namespace App\Services\TemplatePlans\Checks;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;

class CheckDayQueueAvailability
{
    public static function execute(Day $day): bool
    {
        $appointmentsInQueue = Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', AppointmentStatus::QUEUE)
            ->count();

        return $appointmentsInQueue < $day->queue_length;
    }
}