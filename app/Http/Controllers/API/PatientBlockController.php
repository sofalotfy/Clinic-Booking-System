<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Enums\AssistantPermissionsEnum;
use App\Models\Patient;
use App\APIServices\PatientBlocks\ListBlockedPatients;
use App\APIServices\PatientBlocks\BlockPatient;
use App\APIServices\PatientBlocks\UnblockPatient;
use Illuminate\Http\Request;

class PatientBlockController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_BLOCKED_PATIENTS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_BLOCKED_PATIENT->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_BLOCKED_PATIENT->value,
                only: ['destroy']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            "blocked_patients" => ListBlockedPatients::execute($request)
        ]);
    }

    public function store(Request $request, Patient $patient)
    {
        return response()->json([
            'block' => BlockPatient::execute($request, $patient),
        ]);
    }

    public function destroy(Request $request, Patient $patient)
    {
        return response()->json([
            'block' => UnblockPatient::execute($request, $patient),
        ]);
    }
}