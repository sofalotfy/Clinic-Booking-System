<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function test(Request $request)
    {
        return response()->json([
            'success' => true,
            'message' => 'API is working!',
        ]);
    }

}
