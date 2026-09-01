<?php

namespace App\APIServices\Appointments;

use App\Models\Day;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use App\Enums\UserType;
use App\Services\Appointments\Creation\BookAppointment as BookService;

class BookAppointment
{
    public static function execute(Request $request)
    {
        //VALIDATE
        $validated = Validator::make($request->all(), [
            'doctor_id' => ['required', 'exists:doctors,id'],
            'patient_id' => ['required', 'exists:patients,id'],
            'date'      => ['required', 'date'],
        ])->validate();

        //FORMAT DATE WITH CURRENT TIME
        $dateTime = Carbon::parse($validated['date']);

        //GET BOOKING DAY INSTANCE
        $day = Day::where('doctor_id', $validated['doctor_id'])
            ->whereDate('date', $dateTime->toDateString())
            ->first();

        //USE CENTRALIZED SERVICE
        return BookService::execute(
            $request->user(),
            Patient::find($validated['patient_id']),
            $day,
            $dateTime->format('H:i'),
        );
    }
}