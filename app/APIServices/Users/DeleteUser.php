<?php

namespace App\APIServices\Users;

class DeleteUser
{
    public static function execute($request) 
    {
        $user = $request->user();

        if($user->profile){
            $user->profile->delete();
        }
        $user->delete();

        return response()->json([
            'message' => 'Account deleted successfully.',
        ]);
    }
}