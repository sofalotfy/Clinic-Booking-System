<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;
use App\APIServices\Days\GetAvailableSlots;
use App\APIServices\Doctors\GetAvailableDays;
use App\Services\TemplatePlans\ActivatePlan;

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
        ActivatePlan::execute(13);
    
        return response()->json([
            'success' => true,
            'message' => 'Plan created successfully!',
        ]);
    }
}
