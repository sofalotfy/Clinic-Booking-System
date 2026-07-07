<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Flags\AddFlag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class FLagController extends Controller
{
    public function store(Request $request)
    {
        $flag = AddFlag::execute($request);
        
        return response()->json([
            'success' => true,
            'flag' => $flag,
        ]);
    }

    public function flagPatient(Request $request)
    {
         // Allow a single ID or an array of IDs
        $request->merge([
            'patient_ids' => is_array($request->patient_ids)
                ? $request->patient_ids
                : [$request->patient_ids],
        ]);

        $validated = Validator::make($request->all(), [
            'flag_id' => ['required', 'exists:flags,id'],
            'patient_ids' => ['required', 'array', 'min:1'],
            'patient_ids.*' => ['required', 'exists:patients,id'],
        ])->validate();

        $doctorId = $request->user()->doctor->id;

        $rows = [];

        foreach ($validated['patient_ids'] as $patientId) {
            $rows[] = [
                'flag_id' => $validated['flag_id'],
                'patient_id' => $patientId,
                'doctor_id' => $doctorId,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('flag_patient')->insertOrIgnore($rows);
    }

}
