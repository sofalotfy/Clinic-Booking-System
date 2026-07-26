<?php

namespace App\Services\DaysInstances;

use App\Models\Appointment;
use Carbon\Carbon;
use App\Enums\AppointmentStatus;
use App\Services\Appointments\QueueAppointment;
use App\Services\Appointments\ResheduleAppointment;

class ReSheduleDay
{
    public static function execute($day)
    {
        $appointments = Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', [
                AppointmentStatus::ACTIVE,
                AppointmentStatus::QUEUED,
                AppointmentStatus::PENDING,
            ])
            ->orderBy('date')
            ->get();

        // Generate slots
        $slots = [];

        $current = Carbon::parse("{$day->date} {$day->start_time}");
        $end = Carbon::parse("{$day->date} {$day->end_time}");

        while ($current->copy()->addMinutes($day->appointment_duration)->lte($end)) {
            $slots[] = $current->copy();
            $current->addMinutes($day->appointment_duration);
        }

        $used = array_fill(0, count($slots), false);

        foreach ($appointments as $appointment) {

            $appointmentTime = Carbon::parse($appointment->date);

            $slotIndex = self::findClosestAvailableSlot(
                $appointmentTime,
                $slots,
                $used
            );

            if ($slotIndex === null) {
                QueueAppointment::execute(
                    $appointment,
                    $day->appointment_duration,
                    'overflow'
                );

                continue;
            }

            $used[$slotIndex] = true;

            ResheduleAppointment::execute(
                $appointment,
                $slots[$slotIndex]->toDateTimeString(),
                $day->appointment_duration
            );
        }
    }

    /**
     * Returns the index of the closest available slot.
     */
    private static function findClosestAvailableSlot(
        Carbon $appointmentTime,
        array $slots,
        array $used
    ): ?int {

        $count = count($slots);

        if ($count === 0) {
            return null;
        }

        // ---------- Binary Search ----------

        $low = 0;
        $high = $count - 1;

        while ($low <= $high) {

            $mid = intdiv($low + $high, 2);

            if ($slots[$mid]->lt($appointmentTime)) {
                $low = $mid + 1;
            } else {
                $high = $mid - 1;
            }
        }

        // $low is the insertion point.

        $left = $low - 1;
        $right = $low;

        // ---------- Expand outwards ----------

        while ($left >= 0 || $right < $count) {

            while ($left >= 0 && $used[$left]) {
                $left--;
            }

            while ($right < $count && $used[$right]) {
                $right++;
            }

            if ($left < 0) {
                return $right < $count ? $right : null;
            }

            if ($right >= $count) {
                return $left;
            }

            $leftDiff = abs($appointmentTime->diffInSeconds($slots[$left], false));
            $rightDiff = abs($appointmentTime->diffInSeconds($slots[$right], false));

            if ($leftDiff <= $rightDiff) {
                return $left;
            }

            return $right;
        }

        return null;
    }
}