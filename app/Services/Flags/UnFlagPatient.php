<?php

namespace App\Services\Flags;

use App\Models\Flag;
use App\Services\Patients\Checks\IsOldPatient;

class UnFlagPatient
{
    public static function execute($user, $flag, $patient)
    {
        abort_unless(
            IsOldPatient::execute($user->clinicDoctorId(), $patient->id),
            403,
            'You do not have permission to perform this action.'
        );

        $patient->flags()->detach($flag->id, ['doctor_id' => $user->clinicDoctorId()]);

        return true;
    }
}