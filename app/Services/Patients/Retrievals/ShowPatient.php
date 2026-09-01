<?php

namespace App\Services\Patients\Retrievals;

use App\Models\User;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Services\Patients\Checks\IsOldPatient;
use App\Services\Appointments\Retrievals\ListAppointments;

class ShowPatient
{
    public static function execute(User $user, Patient $patient)
    {
        abort_unless(
            IsOldPatient::execute($user->clinicDoctorId(), $patient->id),
            403,
            'You do not have permission to perform this action.'
        );

        return ListAppointments::execute($user, ["patient_id" => $patient->id])
            ->leftJoinSub(
                PatientBlock::query()
                    ->active()
                    ->where('doctor_id', $user->clinicDoctorId()),
                'patient_blocks',
                function ($join) {
                    $join->on('patients.id', '=', 'patient_blocks.patient_id');
                }
            )
            ->groupBy('patients.id');
    }
}