<?php

namespace App\Services\Appointments\Modifications;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Services\Appointments\General\NotifyPatientOfReschedule;
use App\Services\Notifications\NotificationManager;
use App\Enums\NotificationEnum;

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
            NotificationManager::execute($user, $appointment->doctor_id, NotificationEnum::PATIENT_APPOINTMENT_CANCEL, $appointment);
            return $appointment;
        }

        NotifyPatientOfReschedule::execute($user, $appointment, null, $type);

        return $appointment;
    }
}