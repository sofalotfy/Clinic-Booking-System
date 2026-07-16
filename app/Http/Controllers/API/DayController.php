<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Days\GetDays;
use App\APIServices\Days\GetDayAppointments;
use App\Models\Day;

class DayController extends Controller
{
    public function index()
    {
        $days = GetDays::execute();
        
        return response()->json([
            'success' => true,
            'days' => $days,
        ]);
    }

    public function dayAppointments(Request $request)
    {
        
        $appointments = GetDayAppointments::execute($request->date);
        
        return response()->json([
            'success' => true,
            'appointments' => $appointments,
            'day'  => Day::where('date',$request->date)->first(),
        ]);
    }
}
