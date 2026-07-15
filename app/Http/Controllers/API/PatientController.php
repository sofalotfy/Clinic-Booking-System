<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Patients\ListPatients;
use App\APIServices\Patients\GetPatient;
use App\Models\Patient;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'patients' => ListPatients::execute($request->user()->id),
        ]);
    }

    public function show(Patient $patient)
    {
        $patient = GetPatient::execute($patient->id);
        return response()->json([
            'patient' => $patient,
        ]);
    }
}
