<?php

namespace App\APIServices\Days;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class CheckAvailability
{
    public static function execute(int $dayId): bool
    {
        $day = Day::findOrFail($dayId);

        $start = Carbon::parse($day->date . ' ' . $day->start_time);
        $end = Carbon::parse($day->date . ' ' . $day->end_time);

        $totalSlots = intdiv(
            $start->diffInMinutes($end),
            $day->appointment_duration
        );

        $activeAppointments = Appointment::whereDate('date', $day->date)
            ->where('status', AppointmentStatus::ACTIVE)
            ->count();

        return $activeAppointments < $totalSlots;
    }
}