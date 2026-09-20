<?php

namespace App\Services\DaysInstances\Modifications;

use App\Models\Appointment;
use App\Enums\DayStatus;
use App\Services\Appointments\Modifications\CancelAppointment;

class DeleteDay
{
    public static function execute($user, $day)
    {
        $appointments = Appointment::whereDate('date', $day->date)
            ->where('doctor_id', $day->doctor_id)
            ->active()
            ->get();

        foreach ($appointments as $appointment) {
            CancelAppointment::execute($user, $appointment);
        }

        $day->update([
            'status' => DayStatus::CANCELLED,
        ]);
    }
}