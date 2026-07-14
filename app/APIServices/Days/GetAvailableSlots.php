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

        $activeAppointments = Appointment::where('date', $day->date)
            ->where('status', AppointmentStatus::ACTIVE)
            ->orderBy('date')
            ->get();

        $queuedAppointments = Appointment::where('date', $day->date)
            ->where('status', AppointmentStatus::QUEUED)
            ->count();

        // Generate all possible slots
        $slots = [];

        $current = Carbon::createFromFormat('H:i:s', $day->start_time);
        $end = Carbon::createFromFormat('H:i:s', $day->end_time);

        while ($current < $end) {
            $slots[] = $current->copy();
            $current->addMinutes($day->appointment_duration);
        }

        // Remove occupied slots
        foreach ($activeAppointments as $appointment) {
            $appointmentTime = Carbon::createFromFormat(
                'H:i:s',
                $appointment->start_time
            )->format('H:i');

            $slots = array_values(array_filter($slots, function ($slot) use ($appointmentTime) {
                return $slot->format('H:i') !== $appointmentTime;
            }));
        }

        // Return up to 7 available slots
        if (!empty($slots)) {
            return collect($slots)
                ->take(7)
                ->map(function ($slot) {
                    return [
                        'time' => $slot->format('H:i'),
                    ];
                })
                ->values()
                ->toArray();
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