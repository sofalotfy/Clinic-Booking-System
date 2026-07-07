<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Patients\ListPatients;

class PatientController extends Controller
{
    public function index(Request $request)
    {
        return response()->json([
            'patients' => ListPatients::execute($request->user()->id),
        ]);
    }

}
