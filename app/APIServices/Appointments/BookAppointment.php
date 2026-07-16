<?php

namespace App\APIServices\Appointments;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Enums\AppointmentStatus;

class BookAppointment
{
    public static function execute(Request $request): Appointment
    {
        if(!$request->user()->patient){
            throw ValidationException::withMessages([
                'date' => 'the User is not a Patient.',
            ]);
        }
        $appointment = Appointment::where('doctor_id', $request->doctor_id)
                ->where('patient_id', $request->user()->patient->id)
                ->where('date', '<=', Carbon::now()->format('Y-m-d H:i:s'))
                ->whereNotIn('status', [AppointmentStatus::ACTIVE, AppointmentStatus::QUEUED])
                ->first();

        if($appointment){
            throw ValidationException::withMessages([
                'date' => 'You have an appointment with this doctor already.',
            ]);
        }
        
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