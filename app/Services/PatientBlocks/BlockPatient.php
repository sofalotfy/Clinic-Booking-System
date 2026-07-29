<?php

namespace App\Services\PatientBlocks;

use App\Models\PatientBlock;

class BlockPatient
{
    public static function execute(
        int $doctorId,
        int $patientId,
        ?int $blockedBy,
        ?string $reason,
        $expiresAt = null
    ): PatientBlock {
        return PatientBlock::Create(
            [
                'doctor_id' => $doctorId,
                'patient_id' => $patientId,
                'blocked_by' => $blockedBy,
                'reason' => $reason,
                'blocked_at' => now(),
                'expires_at' => $expiresAt,
                'unblocked_at' => null,
                'unblocked_by' => null,
            ]
        );
    }
}