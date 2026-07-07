<?php

namespace App\APIServices\Patients;

use App\Models\Patient;
use App\Models\Appointment;
use App\Enums\UserType;

class GetDoctorPatients
{
    public static function execute(int $doctorId)
    {
        return Patient::leftJoin('appointments', 'patients.id', '=', 'appointments.patient_id')
            ->where('appointments.doctor_id', $doctorId)
            ->distinct()

        
    }
}