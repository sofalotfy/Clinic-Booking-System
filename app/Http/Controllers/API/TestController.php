<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;
use App\APIServices\Days\GetAvailableSlots;
use App\APIServices\Doctors\GetAvailableDays;
USE App\Services\TemplatePlans\ActivatePlan;
use App\Services\TemplatePlans\CountColidingAppoinments;
use App\APIServices\Days\MapAppointments;

class TestController extends Controller
{
    public function test(Request $request)
    {
        $colidingAppointments = MapAppointments::execute();

        return response()->json([
            'appo' => $colidingAppointments,
            'message' => 'Plan activated successfully',
        ]);
    }

    public function makePlan(Request $request)
    {
        
        $plan = CreteaTemplate::execute($request->doctor_id, $request->days);
        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully!',
        ]);
    }
}
