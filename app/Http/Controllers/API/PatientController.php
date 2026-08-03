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
            'patients' => ListPatients::execute($request->user()->id, $request->input('per_page', 15),$request->input('search', null),
                [
                    "age_from" => $request->input('age_from', null),
                    "age_to" => $request->input('age_to', null),
                    "date_from" => $request->input('date_from', null),
                    "date_to" => $request->input('date_to', null),
                    "has_upcoming_appointment" => $request->input('has_upcoming_appointment', null),
                ]),
        ]);
    }

    public function show(Patient $patient)
    {
        $patient = GetPatient::execute(auth()->user()->id, $patient->id);
        return response()->json([
            'patient' => $patient,
        ]);
    }
}
