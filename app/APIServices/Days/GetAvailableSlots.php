<?php

namespace App\APIServices\Days;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetAvailableSlots
{
    public static function execute(int $dayId): array
{
    $day = Day::findOrFail($dayId);

    $activeAppointments = Appointment::whereDate('date', $day->date)
        ->where('status', AppointmentStatus::ACTIVE)
        ->orderBy('date')
        ->get();

    $queuedAppointments = Appointment::whereDate('date', $day->date)
        ->where('status', AppointmentStatus::QUEUED)
        ->count();

    // Generate all possible slots
    $slots = [];

    $current = Carbon::parse($day->date)->setTimeFromTimeString($day->start_time);
    $end = Carbon::parse($day->date)->setTimeFromTimeString($day->end_time);

    while ($current < $end) {
        $slots[] = $current->copy();
        $current->addMinutes($day->appointment_duration);
    }

    // Remove occupied slots
    foreach ($activeAppointments as $appointment) {
        $appointmentTime = Carbon::parse($appointment->date)->format('H:i');

        $slots = array_values(array_filter($slots, function ($slot) use ($appointmentTime) {
            return $slot->format('H:i') !== $appointmentTime;
        }));
    }

    // If there are normal slots available, return them
    if (!empty($slots)) {
        return collect($slots)->map(function ($slot) {
            return [
                'time' => $slot->format('H:i'),
            ];
        })->toArray();
    }

    // Otherwise check the waiting queue
    if ($queuedAppointments < $day->queue_length) {
        return [[
            'type' => 'queue',
            'title' => 'Join Waiting Queue',
        ]];
    }

    // Day is completely full
    return [];
}
}