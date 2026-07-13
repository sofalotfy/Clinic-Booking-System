<?php

namespace App\Services\Appointments;

use App\Models\Appointment;
use Carbon\Carbon;


class GetUpComingAppointment
{
    public static function execute($patient_id, $doctor_id)
    {
        return Appointment::where('patient_id', $patient_id)
            ->where('doctor_id', $doctor_id)
            ->where('date', '>=', Carbon::today()->toDateString())
            ->first();
    }
}