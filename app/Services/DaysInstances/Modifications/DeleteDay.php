<?php

namespace App\Services\DaysInstances\Modifications;

use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Enums\UserType;
use App\Services\Appointments\Modifications\CancelAppointment;

class DeleteDay
{
    public static function execute($user, $day, $shouldCancel = true)
    {
        $appointments = Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->whereIn('status', AppointmentStatus::working())
            ->get();

        if ($shouldCancel) {
            foreach ($appointments as $appointment) {
                CancelAppointment::execute($user, $appointment);
            }
        }

        $day->delete();
    }
}