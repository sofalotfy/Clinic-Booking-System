<?php

namespace App\Services\PatientBlocks;

use App\Models\PatientBlock;
use App\Models\User;

class ListBlockedPatients
{
    public static function execute(User $user){
        return PatientBlock::query()
            ->leftJoin('patients', 'patient_blocks.patient_id', '=', 'patients.id')
            ->leftJoin('users', 'patients.user_id', '=', 'users.id')
            ->where('patient_blocks.doctor_id', $user->clinicDoctor()->id)
            ->active();
    }
}