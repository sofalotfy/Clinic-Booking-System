<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Patient;
use App\Services\PatientBlocks\BlockPatient;
use App\Services\PatientBlocks\UnblockPatient;
use App\APIServices\PatientBlocks\ListBlockedPatients;
use Illuminate\Http\Request;

class PatientBlockController extends Controller
{
    public function index(Request $request)
    {
        $patients = ListBlockedPatients::execute();

        return response()->json(["blocked patients" => $patients]);
    }

    public function store(Request $request, Patient $patient)
    {
        $validated = $request->validate([
            'reason' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['nullable', 'date', 'after:now'],
        ]);

        BlockPatient::execute(
            doctorId: $request->user()->doctor->id,
            patientId: $patient->id,
            blockedBy: $request->user()->id,
            reason: $validated['reason'] ?? null,
            expiresAt: $validated['expires_at'] ?? null,
        );

        return response()->json([
            'success' => true,
            'message' => 'Patient blocked successfully.',
        ]);
    }

    public function destroy(Request $request, Patient $patient)
    {
        UnblockPatient::execute(
            doctorId: $request->user()->doctor->id,
            patientId: $patient->id,
            unblockedBy: $request->user()->id,
        );

        return response()->json([
            'success' => true,
            'message' => 'Patient unblocked successfully.',
        ]);
    }
}