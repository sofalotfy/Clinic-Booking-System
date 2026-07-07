<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    public static function execute(Request $request): Appointment
    {
        $validated = Validator::make($request->all(), [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'date'       => ['required', 'date'],
            'duration'   => ['required', 'integer'],
        ])->validate();

        return Appointment::create([
            'doctor_id' => $validated['doctor_id'],
            'patient_id' => $request->user()->patient->id,
            'date' => $validated['date'],
            'duration' => $validated['duration'],
            // status defaults to pending
        ]);
    }
}