<?php

namespace App\Services\Appointments\Modifications;

use App\Models\Day;
use App\Models\Appointment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;
use Carbon\Carbon;
use Exception;
use App\APIServices\Appointments\UpdateAppointment;
use App\Enums\AppointmentStatus;

class BulkUpdateAppointment
{
    public static function execute(array $updates): void
    {
        DB::transaction(function () use ($updates) {
            $changes = collect($updates)->keyBy('id');

            $appointments = static::fetchAppointments($changes);

            $groups = static::groupAppointmentsByDoctorAndDate($appointments);

            foreach ($groups as $groupAppointments) {
                static::validateGroup($groupAppointments, $changes);
            }

            static::applyUpdates($updates);
        });
    }

    /**
     * Fetch the appointments referenced in the update payload.
     */
    protected static function fetchAppointments(Collection $changes): Collection
    {
        return Appointment::whereIn('id', $changes->keys())->get();
    }

    /**
     * Group appointments by "doctor_id_date" so each group can be
     * validated against the same day's slots.
     */
    protected static function groupAppointmentsByDoctorAndDate(Collection $appointments): array
    {
        $groups = [];

        foreach ($appointments as $appointment) {
            $key = $appointment->doctor_id . '_' . Carbon::parse($appointment->date)->toDateString();

            $groups[$key][] = $appointment;
        }

        return $groups;
    }

    /**
     * Validate that every requested time change within a doctor/date group
     * is a valid, unoccupied slot. Throws if any change is invalid.
     */
    protected static function validateGroup(array $groupAppointments, Collection $changes): void
    {
        $referenceAppointment = $groupAppointments[0];

        $day = static::findDayFor($referenceAppointment);

        $slots = static::generateAvailableSlots($day);

        $occupied = static::getOccupiedSlots($day, $groupAppointments);

        foreach ($groupAppointments as $appointment) {
            static::validateAppointmentChange($appointment, $changes, $slots, $occupied);
        }
    }

    /**
     * Find the Day record for the given appointment's doctor + date.
     */
    protected static function findDayFor(Appointment $appointment): Day
    {
        $date = Carbon::parse($appointment->date)->toDateString();

        return Day::where('doctor_id', $appointment->doctor_id)
            ->where('date', $date)
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
     * Get the times ("H:i" => true) already occupied by appointments on this
     * day that are NOT part of the current update group.
     */
    protected static function getOccupiedSlots(Day $day, array $groupAppointments): array
    {
        $occupied = [];

        $groupIds = collect($groupAppointments)->pluck('id');

        Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereNotIn('id', $groupIds)
            ->whereIn('status', AppointmentStatus::working())
            ->get()
            ->each(function ($appointment) use (&$occupied) {
                $occupied[Carbon::parse($appointment->date)->format('H:i')] = true;
            });

        return $occupied;
    }

    /**
     * Validate a single appointment's requested time change (if any) against
     * the available/occupied slots, marking it occupied once accepted.
     */
    protected static function validateAppointmentChange(
        Appointment $appointment,
        Collection $changes,
        array $slots,
        array &$occupied
    ): void {
        if (!$changes->has($appointment->id)) {
            return;
        }

        $change = $changes[$appointment->id]['changes'];

        if (!isset($change['time'])) {
            return;
        }

        $time = $change['time'];

        if (!isset($slots[$time])) {
            throw new Exception("Invalid slot {$time} for appointment {$appointment->id}");
        }

        if (isset($occupied[$time])) {
            throw new Exception("Slot {$time} is already occupied.");
        }

        $occupied[$time] = true;
    }

    /**
     * Apply all updates now that every requested change has been validated.
     */
    protected static function applyUpdates(array $updates): void
    {
        foreach ($updates as $update) {
            UpdateAppointment::execute($update);
        }
    }
}