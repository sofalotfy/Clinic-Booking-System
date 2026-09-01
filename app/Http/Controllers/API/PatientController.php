<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Enums\AssistantPermissionsEnum;
use Illuminate\Http\Request;
use App\APIServices\Patients\ListPatients;
use App\APIServices\Patients\ShowPatient;
use App\APIServices\Patients\FlagPatient;
use App\APIServices\Patients\UnFlagPatient;
use App\APIServices\Patients\BulkFlagPatient;
use App\APIServices\Patients\NotePatient;
use App\Models\Patient;
use App\Models\Flag;



class PatientController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PATIENTS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_PATIENT->value,
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_FLAG->value . ',flag',
                only: ['flagPatient']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_FLAG->value . ',flag',
                only: ['unflagPatient']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_FLAG->value . ',flag',
                only: ['bulkFlag']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_NOTE->value,
                only: ['notePatient']
            ),
        ];
    }

    public function index(Request $request)
    {
        return ListPatients::execute($request);
    }

    public function show(Request $request, Patient $patient)
    {
        return ShowPatient::execute($request, $patient);
    }

    public function flagPatient(Request $request, Patient $patient, Flag $flag)
    {
        return FlagPatient::execute($request, $patient, $flag);
    }

    public function unflagPatient(Request $request, Patient $patient, Flag $flag)
    {
        return UnFlagPatient::execute($request, $patient, $flag);
    }

    public function bulkFlag(Request $request, Flag $flag)
    {
        return BulkFlagPatient::execute($request, $flag);
    }

    public function notePatient(Request $request, Patient $patient)
    {
        return NotePatient::execute($request, $patient);
    }
}
