<?php

namespace App\Services\Patients\Retrievals;

use App\Models\User;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Services\Patients\Checks\IsOldPatient;

class ShowPatient
{
    public static function execute(User $user, Patient $patient, $filters = null)
    {
        abort_unless(
            IsOldPatient::execute($user->clinicDoctorId(), $patient->id),
            403,
            'You do not have permission to perform this action.'
        );

        return Patient::query()
            ->where('patients.id', $patient->id)
            ->leftJoin('appointments', 'appointments.patient_id', '=', 'patients.id')
            ->leftJoin('users', 'patients.user_id', '=', 'users.id')
            ->where('appointments.doctor_id', $user->clinicDoctorId())
            ->leftJoinSub(
                PatientBlock::query()
                    ->active()
                    ->where('doctor_id', $user->clinicDoctorId()),
                'patient_blocks',
                fn ($join) => $join->on('patients.id', '=', 'patient_blocks.patient_id')
            )
            ->groupBy('patients.id');
    }
}