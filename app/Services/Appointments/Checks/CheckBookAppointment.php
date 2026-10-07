<?php

namespace App\Services\Appointments\Checks;

use App\Enums\AppointmentStatus;
use App\Models\Day;
use App\Services\Patients\Checks\CheckBookingLimitExceeded;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotExistance;
use Carbon\Carbon;

class CheckBookAppointment
{
    /**
     * Fast-fail, side-effect-free check run BEFORE any account is created.
     * Mirrors the checks the booking service runs under lock, so keep the two
     * in sync (the service is the source of truth).
     *
     * Returns ['valid' => bool, 'message' => ?string, 'day' => ?Day].
     */
    public static function execute($user, $patient, $date, $status = AppointmentStatus::ACTIVE): array
    {
        $dateTime = Carbon::parse($date);

        $day = Day::where('doctor_id', $user->clinicDoctorId())
            ->whereDate('date', $dateTime->toDateString())
            ->active()
            ->first();

        if (! $day) {
            return self::verdict(false, 'The day is not available.');
        }

        $time = $dateTime->format('H:i');

        if ($status === AppointmentStatus::ACTIVE && $dateTime->isPast()) {
            return self::verdict(false, 'This time has already passed.');
        }

        // New patients ($patient === null) have no appointments yet, so skip the limit.
        if ($patient && CheckBookingLimitExceeded::execute($day->doctor_id, $patient->id)) {
            return self::verdict(false, 'You have reached the maximum number of appointments.');
        }

        if ($status === AppointmentStatus::ACTIVE) {
            if (! CheckSlotExistance::execute($day, $time)) {
                return self::verdict(false, 'This slot is not available.');
            }

            if (! CheckSlotAvailability::execute($day, $time)) {
                return self::verdict(false, 'This slot is already booked.');
            }
        } elseif (! CheckAvailability::execute($day)) {
            return self::verdict(false, 'The Day is fully booked.');
        }

        return self::verdict(true, null, $day);
    }

    private static function verdict(bool $valid, ?string $message = null, ?Day $day = null): array
    {
        return [
            'valid' => $valid,
            'message' => $message,
            'day' => $day,
        ];
    }
}
