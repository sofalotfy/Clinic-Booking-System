<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Services\Notifications\NotificationManager;
use App\Enums\NotificationEnum;
use App\Services\Appointments\Modifications\UnConfirmAppointment;

class CancelAppointment
{
    public static function execute($user, $appointment, $type = AppointmentUpdateNotificationTypes::CANCEL)
    {
        $appointment->update(
            [
                'status'  =>  AppointmentStatus::CANCELLED,
            ]
        );

        if ($user->isPatient()) {
            $notification = NotificationEnum::PATIENT_APPOINTMENT_CANCEL;
        }else{
            $notification = NotificationEnum::DOCTOR_APPOINTMENT_CANCEL;
            UnConfirmAppointment::execute($user, $appointment);
        }

        NotificationManager::execute($user, $appointment->doctor_id, $notification, $appointment);
        return $appointment;
    }
}