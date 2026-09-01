<?php

namespace App\APIServices\PatientBlocks;

use App\Models\Patient;
use Illuminate\Http\Request;
use App\Services\PatientBlocks\BlockPatient as BlockService;

class BlockPatient
{
    public static function execute(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        return BlockService::execute(
            user: $request->user(),
            patient: $patient,
            reason: $validated['reason'] ?? null,
            expiresAt: $validated['expires_at'] ?? null,
        );
    }
}