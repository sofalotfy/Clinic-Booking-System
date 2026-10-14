<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class UpdateAppointment 
{
    public static function execute($user, $appointment, $status = null, $date = null, $grade = null)
    {
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
                if ($date) { // RESCHEDULE APPOINTMENT
                    $newDate = self::formatDate($date);
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

        if ($date) {
            $appointment->update([
                "date"  => self::formatDate($date),
            ]);
        }
        
        return $appointment;
    }

    private static function formatDate(string $date): string
    {
        return Carbon::parse($date)->toDateTimeString();
    }
}