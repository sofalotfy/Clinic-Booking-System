<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    public function test(Request $request)
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        return response()->json([
            'success' => true,
            'phone' => $request->phone,
            'message' => 'API is working!',
        ]);
    }

}
