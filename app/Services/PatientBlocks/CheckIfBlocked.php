<?php

namespace App\Services\PatientBlocks;

use App\Models\PatientBlock;

class CheckIfBlocked
{
    public static function execute(int $doctorId, int $patientId): bool
    {
        return PatientBlock::query()
            ->where('doctor_id', $doctorId)
            ->where('patient_id', $patientId)
            ->where(function ($query) {
                $query->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            })
            ->exists();
    }
}