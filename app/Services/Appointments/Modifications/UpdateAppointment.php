<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use Carbon\Carbon;

class UpdateAppointment 
{
    public static function execute($user, $appointment, $status = null, $time = null, $grade = null)
    {
        GradeAppointment::execute($user, $appointment, $grade);
        
        if ($time) {
            $appointment->update([
                "date"  => self::formatDate($appointment->date, $time),
            ]);
        }
        
        $status = $status?AppointmentStatus::from($status):$appointment->status;

        switch ($status) {

            case AppointmentStatus::CANCELLED://CANCEL APPOINTMENT
                CancelAppointment::execute($user, $appointment);
                break;

            case AppointmentStatus::QUEUED://QUEUE APPOINTMENT
                QueueAppointment::execute($user, $appointment);
                break;

            case AppointmentStatus::ACTIVE:

                if ($time) { // RESCHEDULE APPOINTMENT
                    $newDate = self::formatDate($appointment->date, $time);
                    ResheduleAppointment::execute($user, $appointment, $newDate, $appointment->duration);
                } else { //ACTIVATE APPOINTMENT
                    ActivateAppointment::execute($user, $appointment);
                }
                break;

            case AppointmentStatus::DONE:
                FinnishAppointment::execute($user, $appointment);
                break;

            case AppointmentStatus::PENDING:
                DeActivateAppointment::execute($user, $appointment);
                break;
        }

        return $appointment;
    }

    private static function formatDate(string $dateTime, string $time): string
    {
        return Carbon::parse($dateTime)
            ->setTimeFromTimeString($time)
            ->toDateTimeString();
    }
}