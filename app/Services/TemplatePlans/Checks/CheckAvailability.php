<?php

namespace App\Services\TemplatePlans\Checks;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class CheckAvailability
{
    public static function execute(Day $day): bool
    {
        $start = Carbon::parse($day->date . ' ' . $day->start_time);
        $end = Carbon::parse($day->date . ' ' . $day->end_time);

        $totalSlots = intdiv(
            $start->diffInMinutes($end),
            $day->appointment_duration
        );

        $activeAppointments = Appointment::whereDate('date', $day->date)
            ->whereIn('status', AppointmentStatus::working())
            ->count();

        return $activeAppointments < ($totalSlots + $day->queue_length);
    }
}