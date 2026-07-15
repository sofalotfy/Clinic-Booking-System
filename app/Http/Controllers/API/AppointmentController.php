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
        $appointment = BookAppointment::execute($request);
        
        return response()->json([
            'success' => true,
            'appointment' => $appointment,
        ]);
    }

    public function update(Request $request)
    {
        $appointments = [];

        if ($request->has('appointments') && is_array($request->input('appointments'))) {
            $appointments = $request->input('appointments');
        } elseif ($request->has('data') && is_array($request->input('data'))) {
            $appointments = $request->input('data');
        } elseif (is_array($request->all())) {
            if (isset($request->all()[0])) {
                $appointments = $request->all();
            } elseif ($request->has('id')) {
                $appointments = [$request->all()];
            }
        }

        \Log::info([
            'all' => $request->all(),
            'json' => $request->json()->all(),
            'content' => $request->getContent(),
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
