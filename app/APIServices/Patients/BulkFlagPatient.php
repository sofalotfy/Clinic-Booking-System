<?php

namespace App\APIServices\Patients;

use App\Models\Flag;
use Illuminate\Support\Facades\Validator;
use App\Services\Flags\BulkFlagPatient as BulkFlagService;

class BulkFlagPatient
{
    public static function execute($request, Flag $flag)
    {
        $request->merge([
            'patient_ids' => is_array($request->patient_ids)
                ? $request->patient_ids
                : [$request->patient_ids],
        ]);

        $validated = Validator::make($request->all(), [
            'patient_ids' => ['required', 'array', 'min:1'],
            'patient_ids.*' => ['required', 'exists:patients,id'],
        ])->validate();

        return BulkFlagService::execute($request->user(), $flag, $validated['patient_ids']);
    }
}