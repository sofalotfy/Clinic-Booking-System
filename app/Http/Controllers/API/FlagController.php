<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Flags\AddFlag;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use App\APIServices\Flags\UnFlag;
use App\APIServices\Flags\DeleteFlag;
use App\Models\Flag;

class FLagController extends Controller
{
    public function index(Request $request)
    {
        $flags = DB::table('flags')->where('doctor_id', $request->user()->doctor->id)->get();
        
        return response()->json([
            'success' => true,
            'flags' => $flags,
        ]);
    }
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

        return response()->json([
            'success' => true,
            'message' => 'Flag attached successfully',
        ]);
    }

    public function unflagPatient(Request $request)
    {
        UnFlag::execute($request->flag_id, $request->patient_id);

        return response()->json([
            'success' => true,
            'message' => 'Flag detached successfully',
        ]);
    }

    public function destroy(Flag $flag)
    {
        DeleteFlag::execute($flag->id);
        
        return response()->json([
            'success' => true,
            'message' => 'Flag deleted successfully',
        ]);
    }

}
