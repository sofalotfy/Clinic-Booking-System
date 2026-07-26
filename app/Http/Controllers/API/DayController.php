<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Days\GetDays;
use App\APIServices\Days\GetDayAppointments;
use App\APIServices\Days\MapAppointments;
use App\Services\DaysInstances\UpdateDay;
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

    public function update(Day $day, Request $request)
    {
        UpdateDay::execute($day, $request->day);

        return response()->json([
            'success' => true,
            'message' => 'Day updated successfully',
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

    public function mapAppointments(Request $request)
    {
        $days = MapAppointments::execute();

        return response()->json([
            "success"  => true,
            "days"     => $days,
        ]);
    }
}
