<?php

namespace App\APIServices\Patients;

use App\Models\Patient;
use App\Models\Appointment;
use App\Enums\UserType;

class GetDoctorPatients
{
    public static function execute(int $doctorId)
    {
        return Patient::query()
            ->leftJoin('appointments', function ($join) use ($doctorId) {
                $join->on('patients.id', '=', 'appointments.patient_id')
                    ->where('appointments.doctor_id', $doctorId);
            })
            ->leftJoin('patient_blocks', function ($join) use ($doctorId) {
                $join->on('patients.id', '=', 'patient_blocks.patient_id')
                    ->where('patient_blocks.doctor_id', $doctorId)
                    ->whereNull('patient_blocks.unblocked_at') // if you implement soft unblocking
                    ->where(function ($query) {
                        $query->whereNull('patient_blocks.expires_at')
                            ->orWhere('patient_blocks.expires_at', '>', now());
                    });
            })
            ->select([
                'patients.*',
                'patient_blocks.id as block_id',
                'patient_blocks.reason as block_reason',
                'patient_blocks.blocked_at',
                'patient_blocks.expires_at',
            ])
            ->whereNotNull('appointments.id')
            ->distinct();
    }
}