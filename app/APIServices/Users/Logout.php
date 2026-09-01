<?php

namespace App\APIServices\Users;

class Logout
{
    public static function execute($request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged out'
        ]);
    }
}
