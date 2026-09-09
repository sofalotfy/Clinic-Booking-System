<?php

namespace App\APIServices\Appointments;

use App\Models\Day;
use App\Models\Patient;
use App\Models\User;
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
        // VALIDATE
        $validated = Validator::make($request->all(), [
            'phone' => ['required'],
            'name'  => ['required'],
            'age'   => ['required', 'numeric', 'min:1'],
            'area'  => ['required'],
            'date'  => ['required', 'date'],
        ])->validate();

        // FORMAT DATE WITH CURRENT TIME
        $dateTime = Carbon::parse($validated['date']);

        // GET BOOKING DAY INSTANCE
        $day = Day::where('doctor_id', $request->user()->clinicDoctorId())
            ->whereDate('date', $dateTime->toDateString())
            ->first();

        // FETCH USER BY PHONE
        $user = User::where('phone', $validated['phone'])->first();

        // CREATE USER AND PATIENT IF USER DOES NOT EXIST
        if (!$user) {
            $user = User::create([
                'phone' => $validated['phone'],
                'name'  => $validated['name'],
                'age'   => $validated['age'],
                'area'  => $validated['area'],
                'type'  => UserType::PATIENT,
            ]);

            Patient::create([
                'user_id' => $user->id,
            ]);
        }

        // EXISTING USER MUST BE A PATIENT
        if (!$user->isPatient()) {
            throw ValidationException::withMessages([
                'phone' => 'This phone number is not registered as a patient.',
            ]);
        }

        // GET PATIENT ACCOUNT
        $patient = $user->patient;

        // USE CENTRALIZED SERVICE
        return BookService::execute(
            $request->user(),
            $patient,
            $day,
            $dateTime->format('H:i'),
        );
    }
}