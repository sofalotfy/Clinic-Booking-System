<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Services\Appointments\Checks\ResolveAppointmentType;
use Carbon\Carbon;

class UpdateAppointment 
{
    public static function execute($user, $appointment, $status = null, $date = null, $time = null, $grade = null)
    {
        $newDate = self::resolveDate($appointment->date, $date, $time);

        GradeAppointment::execute($user, $appointment, $grade);
        
        $status = $status?AppointmentStatus::from($status):$appointment->status;

        switch ($status) {

            case AppointmentStatus::CANCELLED://CANCEL APPOINTMENT
                CancelAppointment::execute($user, $appointment);
                break;

            case AppointmentStatus::QUEUED://QUEUE APPOINTMENT
                QueueAppointment::execute($user, $appointment);
                break;

            case AppointmentStatus::ACTIVE:
                ActivateAppointment::execute($user, $appointment);
                if ($newDate) { // RESCHEDULE APPOINTMENT
                    ResheduleAppointment::execute($user, $appointment, $newDate, $appointment->duration);
                }
                break;

            case AppointmentStatus::DONE:
                FinnishAppointment::execute($user, $appointment);
                break;

            case AppointmentStatus::PENDING:
                DeActivateAppointment::execute($user, $appointment);
                break;
        }

        if ($newDate) {
            $appointment->update([
                "date"  => $newDate,
                "type"  => ResolveAppointmentType::execute($appointment->doctor_id, $appointment->patient, $newDate),
            ]);
        }
        
        return $appointment;
    }

    /**
     * Merge a new date (Y-m-d) and/or time (H:i[:s]) into the current
     * datetime. Whichever is omitted keeps its current value. Returns null
     * when neither is given.
     */
    public static function resolveDate(string $current, ?string $date, ?string $time): ?string
    {
        if (! $date && ! $time) {
            return null;
        }

        $resolved = Carbon::parse($current);

        if ($date) {
            $resolved->setDateFrom(Carbon::parse($date));
        }

        if ($time) {
            $resolved->setTimeFromTimeString($time);
        }

        return $resolved->toDateTimeString();
    }
}