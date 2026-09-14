<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use App\Enums\AssistantPermissionsEnum;
use App\APIServices\Clinics\ListClinics;
use App\APIServices\Clinics\AddClinic;
use App\APIServices\Clinics\ShowClinic;
use App\APIServices\Clinics\UpdateClinic;
use Illuminate\Http\Request;
use App\Models\Clinic;

class ClinicController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_CLINICS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_CLINIC->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_CLINIC->value . ',clinic',
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_CLINIC->value . ',clinic',
                only: ['update']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'clinics' => ListClinics::execute($request),
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'clinic' => AddClinic::execute($request),
        ]);
    }

    public function show(Request $request, Clinic $clinic)
    {
        return response()->json([
            'success' => true,
            'clinic' => ShowClinic::execute($request, $clinic),
        ]);
    }

    public function update(Request $request, Clinic $clinic)
    {        
        return response()->json([
            'success' => true,
            'clinic' => UpdateClinic::execute($request, $clinic),
        ]);
    }
}
