<?php

namespace App\Services\PatientBlocks;

use App\Models\PatientBlock;
use App\Models\User;
use App\Models\Patient;

class BlockPatient
{
    public static function execute(User $user, Patient $patient, ?string $reason, $expiresAt = null)
    {
        if ($patient->isBlocked($user->clinicDoctor())) {
            throw new \Exception('Patient is already blocked');
        }

        return PatientBlock::Create(
            [
                'doctor_id'  => $user->clinicDoctor()->id,
                'patient_id' => $patient->id,
                'blocked_by' => $user->id,
                'reason' => $reason,
                'blocked_at' => now(),
                'expires_at' => $expiresAt,
                'unblocked_at' => null,
                'unblocked_by' => null,
            ]
        );
    }
}