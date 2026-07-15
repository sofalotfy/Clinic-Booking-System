<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\TemplatePlans\CreteaTemplate;

class TemplatePlanController extends Controller
{
    public function store(Request $request)
    {
        $plan = CreteaTemplate::execute($request->name, $request->description, $request->days);
        return response()->json([
            'plan' => $plan,
        ]);
    }
}
