<?php

namespace App\APIServices\Days;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class GetAvailableSlots
{
    public static function execute(int $dayId): array
{
    $day = Day::findOrFail($dayId);

    $activeAppointments = Appointment::whereDate('date', $day->date)
        ->where('status', AppointmentStatus::ACTIVE)
        ->get();

    $queuedAppointments = Appointment::whereDate('date', $day->date)
        ->where('status', AppointmentStatus::QUEUED)
        ->count();

    // Build a lookup table of occupied times
    $occupied = $activeAppointments
        ->map(function ($appointment) {
            return Carbon::parse($appointment->date)->format('H:i');
        })
        ->flip()
        ->all();

    $slots = [];

    $current = Carbon::parse($day->date)
        ->setTimeFromTimeString($day->start_time);

    $end = Carbon::parse($day->date)
        ->setTimeFromTimeString($day->end_time);

    while ($current < $end) {

        if (!isset($occupied[$current->format('H:i')])) {
            $slots[] = [
                'time' => $current->format('H:i'),
            ];
        }

        $current->addMinutes((int) $day->appointment_duration);
    }

    // Normal available slots
    if (!empty($slots)) {
        return $slots;
    }

    // No slots left, offer waiting queue if possible
    if ($queuedAppointments < $day->queue_length) {
        return [[
            'type'  => 'queue',
            'title' => 'Join Waiting Queue',
        ]];
    }

    // Day completely full
    return [];
}
}