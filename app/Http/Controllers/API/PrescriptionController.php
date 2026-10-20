<?php

namespace App\Http\Controllers\API;

use App\APIServices\Prescriptions\DeletePrescription;
use App\APIServices\Prescriptions\ListPrescriptions;
use App\APIServices\Prescriptions\ShowPrescription;
use App\APIServices\Prescriptions\StorePrescription;
use App\APIServices\Prescriptions\UpdatePrescription;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Models\Prescription;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class PrescriptionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PRESCRIPTIONS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_PRESCRIPTION->value . ',prescription',
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_PRESCRIPTION->value . ',appointment',
                only: ['showForAppointment']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_PRESCRIPTION->value . ',appointment',
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_PRESCRIPTION->value . ',prescription',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_PRESCRIPTION->value . ',prescription',
                only: ['destroy']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'prescriptions' => ListPrescriptions::execute($request),
        ]);
    }

    public function show(Prescription $prescription)
    {
        return response()->json([
            'success' => true,
            'prescription' => ShowPrescription::execute($prescription),
        ]);
    }

    public function showForAppointment(Appointment $appointment)
    {
        return response()->json([
            'success' => true,
            'prescription' => ShowPrescription::execute($appointment->prescription),
        ]);
    }

    public function store(Request $request, Appointment $appointment)
    {
        return response()->json([
            'success' => true,
            'prescription' => StorePrescription::execute($request, $appointment),
        ], 201);
    }

    public function update(Request $request, Prescription $prescription)
    {
        return response()->json([
            'success' => true,
            'prescription' => UpdatePrescription::execute($request, $prescription),
        ]);
    }

    public function destroy(Prescription $prescription)
    {
        DeletePrescription::execute($prescription);

        return response()->json([
            'success' => true,
            'message' => 'Prescription deleted successfully',
        ]);
    }
}
