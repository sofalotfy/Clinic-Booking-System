<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    protected $table = "doctor_whatsapp_accounts";

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
