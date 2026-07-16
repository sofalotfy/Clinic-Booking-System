<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Days\GetDays;
use App\APIServices\Days\GetDayAppointments;
use App\Models\Day;

class DayController extends Controller
{
    public function index(Request $request)
    {
        $days = GetDays::execute([
            "status" => $request->status,
            'date_from'  =>  $request->date_from,
            'date_to'  =>  $request->date_to,
        ]);
        
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
            'day'  => Day::where('date',$request->date)->where('doctor_id',auth()->user()->doctor->id)->first(),
        ]);
    }
}
