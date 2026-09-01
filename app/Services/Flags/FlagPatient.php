<?php

namespace App\Services\Flags;

use App\Models\Flag;
use App\Models\Patient;
use App\Services\Patients\Checks\IsOldPatient;

class FlagPatient
{
    public static function execute(User $user, Patient $patient, Flag $flag)
    {
        abort_unless(
            IsOldPatient::execute($user->clinicDoctorId(), $patient->id),
            403,
            'You do not have permission to perform this action.'
        );
        if($patient->flags()->where('flag_id', $flag->id)->exists()){
            return $patient->refresh()->flags;
        }

        $patient->flags()->attach($flag->id, ['doctor_id' => $user->clinicDoctorId()]);
        return $patient->refresh()->flags;
    }
}