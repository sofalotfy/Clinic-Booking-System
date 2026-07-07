<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Appointments\AddFlag;

class FLagController extends Controller
{
    public function index(Request $request)
    {
        $flag = AddFlag::execute($request);
        
        return response()->json([
            'success' => true,
            'flag' => $flag,
        ]);
    }

}
