<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Appointments\BookAppointment;
use App\APIServices\Appointments\ListAppointments;
use App\APIServices\Appointments\UpdateAppointment;

class AppointmentController extends Controller
{

    public function index()
    {
        // list all appointments for patient
        $appointments = ListAppointments::execute();
        
        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

    public function store(Request $request)
    {
        if(!$request->user()->patient){
            throw ValidationException::withMessages([
                'date' => 'the User is not a Patient.',
            ]);
        }
        $appointment = BookAppointment::execute($request);
        
        return response()->json([
            'success' => true,
            'appointment' => $appointment,
        ]);
    }

    public function update(Request $request)
    {
        \Log::info([
            'all' => $request->all(),
        ]);
        $appointments = $request->all();

        $appointments = $appointments['updates'];

        \Log::info([
            'appointments' => $appointments,
        ]);

        foreach ($appointments as $appointment) {
            if (is_array($appointment) && isset($appointment['id'])) {
                UpdateAppointment::execute($appointment);
            }
        }
        
        return response()->json([
            'success' => true,
        ]);
    }
}
