<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Flags\AddFlag;

class FLagController extends Controller
{
    public function store(Request $request)
    {
        $flag = AddFlag::execute($request);
        
        return response()->json([
            'success' => true,
            'flag' => $flag,
        ]);
    }

}
