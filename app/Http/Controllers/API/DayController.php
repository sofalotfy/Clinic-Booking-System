<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Days\GetDays;

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
}
