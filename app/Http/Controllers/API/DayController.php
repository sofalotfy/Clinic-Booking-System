<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\APIServices\Days\ListDays;
use App\APIServices\Days\ShowDay;
use App\APIServices\Days\UpdateDay;
use App\APIServices\Days\GetDayAppointments;
use App\APIServices\Days\MapAppointments;
use Illuminate\Http\Request;
use App\Models\Day;

class DayController extends Controller implements HasMiddleware
{
    public static function middleware(): array 
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PLANS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_PLAN->value . ',day',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_APPOINTMENTS->value,
                only: ['dayAppointments']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_APPOINTMENTS->value,
                only: ['mapAppointments']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'days' => ListDays::execute($request),
        ]);
    }

    public function update(Day $day, Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'Day updated successfully',
            'day'     => UpdateDay::execute($day, $request),
        ]);
    }

    public function dayAppointments(Request $request)
    {
        return response()->json([
            'success'      => true,
            'appointments' => GetDayAppointments::execute($request),
            'day'          => ShowDay::execute($request, Day::where('date',$request->date)->where('doctor_id',$request->user()->clinicDoctorId())->first()),
        ]);
    }

    public function mapAppointments(Request $request)
    {
        return response()->json([
            "success"  => true,
            "days"     => MapAppointments::execute($request),
        ]);
    }
}
