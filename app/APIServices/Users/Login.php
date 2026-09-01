<?php

namespace App\APIServices\Users;

use Illuminate\Support\Facades\Auth;

class Login
{
    public static function execute($request)
    {
        // 1. Validate input
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // 2. Try to login user
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => 'Invalid credentials'
            ], 401);
        }

        $response = ShowUser::execute($request, Auth::user());
        $data = $response->getData(true);
        $data['token'] = Auth::user()->createToken('api-token')->plainTextToken;
        $response->setData($data);
        return $response;
    }
}
