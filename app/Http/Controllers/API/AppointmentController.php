<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Appointments\BookAppointment;
use App\APIServices\Appointments\ListAppointments;
use App\APIServices\Appointments\UpdateAppointment;
use App\APIServices\Appointments\BulkUpdateAppointments;
use App\Models\Appointment;

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

    public function update(Request $request, Appointment $appointment)
    {
        $appointment = UpdateAppointment::execute($appointment,$request->all());
        
        return response()->json([
            'success' => true,
            'appointment' => $appointment,
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        $appointments = BulkUpdateAppointments::execute($request);
        
        return response()->json([
            'success' => true,
            'appointments' => $appointments,
        ]);
    }

}
