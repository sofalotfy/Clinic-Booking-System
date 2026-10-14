<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Models\Appointment;
use App\Models\Day;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class BulkUpdateAppointment
{
    /**
     * Each update is ['id' => int, 'changes' => ['status'?, 'date'?, 'grade'?]],
     * where date is the full new date and time (Y-m-d H:i[:s]).
     */
    public static function execute($user, array $updates): void
    {
        DB::transaction(function () use ($user, $updates) {
            $changes = collect($updates)->keyBy('id');

            $appointments = Appointment::whereIn('id', $changes->keys())->get()->keyBy('id');

            static::validateDates($appointments, $changes);

            foreach ($changes as $id => $update) {
                $change = $update['changes'] ?? [];

                UpdateAppointment::execute(
                    $user,
                    $appointments[$id],
                    $change['status'] ?? null,
                    $change['date'] ?? null,
                    $change['grade'] ?? null
                );
            }
        });
    }

    /**
     * Validate that every requested new date is a valid, unoccupied slot on
     * a scheduled day of the appointment's doctor. Throws if any is invalid.
     */
    protected static function validateDates(Collection $appointments, Collection $changes): void
    {
        $ids = $appointments->keys();

        // Slots already claimed by earlier changes in this batch, "doctor|Y-m-d H:i" => true
        $claimed = [];

        // Per doctor/day cache of "H:i" => true slots and of untouched occupied times
        $slotsByDay = [];
        $occupiedByDay = [];

        foreach ($changes as $id => $update) {
            $date = $update['changes']['date'] ?? null;

            if (! $date) {
                continue;
            }

            $appointment = $appointments[$id];
            $target = Carbon::parse($date);
            $dayKey = $appointment->doctor_id.'|'.$target->toDateString();

            if (! isset($slotsByDay[$dayKey])) {
                $day = static::findDay($appointment->doctor_id, $target);

                $slotsByDay[$dayKey] = static::generateAvailableSlots($day);
                $occupiedByDay[$dayKey] = static::getOccupiedSlots($day, $ids);
            }

            $time = $target->format('H:i');
            $claimKey = $dayKey.'|'.$time;

            if (! isset($slotsByDay[$dayKey][$time])) {
                throw new Exception("Invalid slot {$target->toDateTimeString()} for appointment {$id}");
            }

            if (isset($occupiedByDay[$dayKey][$time]) || isset($claimed[$claimKey])) {
                throw new Exception("Slot {$target->toDateTimeString()} is already occupied.");
            }

            $claimed[$claimKey] = true;
        }
    }

    protected static function findDay(int $doctorId, Carbon $date): Day
    {
        return Day::where('doctor_id', $doctorId)
            ->where('date', $date->toDateString())
            ->firstOrFail();
    }

    /**
     * Generate all valid appointment start times ("H:i" => true) for a day,
     * based on its start/end time and appointment duration.
     */
    protected static function generateAvailableSlots(Day $day): array
    {
        $slots = [];

        $current = Carbon::parse("{$day->date} {$day->start_time}");
        $end = Carbon::parse("{$day->date} {$day->end_time}");

        while ($current->copy()->addMinutes($day->appointment_duration)->lte($end)) {
            $slots[$current->format('H:i')] = true;

            $current->addMinutes($day->appointment_duration);
        }

        return $slots;
    }

    /**
     * Get the times ("H:i" => true) held on this day by working appointments
     * that are NOT part of this update, since those are moving anyway.
     */
    protected static function getOccupiedSlots(Day $day, Collection $updatedIds): array
    {
        $occupied = [];

        Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereNotIn('id', $updatedIds)
            ->whereIn('status', AppointmentStatus::working())
            ->get()
            ->each(function ($appointment) use (&$occupied) {
                $occupied[Carbon::parse($appointment->date)->format('H:i')] = true;
            });

        return $occupied;
    }
}
