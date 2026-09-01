<?php

namespace App\Services\PatientBlocks;

use App\Models\PatientBlock;
use App\Models\Patient;
use App\Models\User;

class UnblockPatient
{
    public static function execute(User $user, Patient $patient)
    {
        return PatientBlock::where([
            'patient_id' => $patient->id,
            'doctor_id'  => $user->clinicDoctor()->id,
        ])
        ->active()
        ->update([
            'unblocked_at' => now(),
            'unblocked_by' => $user->id,
        ]);
    }
}