<?php

namespace App\APIServices\Flags;

use App\Models\Flag;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UnFlag
{
    public static function execute(int $flag_id, int $patient_id): void
    {
        $doctorId = $request->user()->doctor->id;

        // Ensure the flag belongs to the authenticated doctor
        $flag = Flag::where('id', $flag_id)
            ->where('doctor_id', $doctorId)
            ->first();

        if (! $flag) {
            throw ValidationException::withMessages([
                'flag_id' => ['The selected flag is invalid.'],
            ]);
        }

        // Remove the flag from the patient
        $flag->patients()->detach($patient_id);
    }
}