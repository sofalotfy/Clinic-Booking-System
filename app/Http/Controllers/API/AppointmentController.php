<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Appointments\BookAppointment;
use App\APIServices\Appointments\ListAppointments;

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
        $appointment = BookAppointment::execute($request);
        
        return response()->json([
            'success' => true,
            'appointment' => $appointment,
        ]);
    }

}
