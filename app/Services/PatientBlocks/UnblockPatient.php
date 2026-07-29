<?php

namespace App\Services\PatientBlocks;

use App\Models\PatientBlock;

class UnblockPatient
{
    public static function execute(int $doctorId, int $patientId, int $unblockedBy): void
    {
        PatientBlock::where([
            'doctor_id' => $doctorId,
            'patient_id' => $patientId,
        ])->update([
            'unblocked_at' => now(),
            'unblocked_by' => $unblockedBy,
        ]);
    }
}