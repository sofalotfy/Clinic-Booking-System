<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Services\Notifications\NotificationManager;
use App\Enums\NotificationEnum;
use App\Services\Appointments\Modifications\UnConfirmAppointment;

class ResheduleAppointment
{
    public static function execute($user, $appointment, $new_date, $duration = null,  $cause = AppointmentUpdateNotificationTypes::COLIDE)
    {
        $appointment->update(
            [
                'date' => $new_date,
                'duration' => $duration ?? $appointment->duration,
                'status'  =>  AppointmentStatus::ACTIVE,
                'isConfirmed' => false,
            ]
        );

        if ($user->isPatient()) {
            $notification = NotificationEnum::PATIENT_APPOINTMENT_RESCHEDULED;
        }else{
            $notification = NotificationEnum::DOCTOR_APPOINTMENT_RESCHEDULED;
            UnConfirmAppointment::execute($user, $appointment);
        }

        NotificationManager::execute($user, $appointment->doctor_id, $notification, $appointment);

        return $appointment;
    }
}