<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;
use App\APIServices\Days\GetAvailableSlots;
use App\APIServices\Doctors\GetAvailableDays;

class TestController extends Controller
{
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'slots' => GetAvailableSlots::execute(6),
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
