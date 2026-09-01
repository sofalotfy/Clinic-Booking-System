<?php

namespace App\APIServices\Users;

use App\Models\User;

class ShowUser
{
    public static function execute($request, $user)
    {
        $user = $user->select(self::getSelects())->where('id', $user->id)->first();
        
        $user->image = isset($user->image) && $user->image ? asset('storage/' . $user->image) : null;

        return response()->json([
            'user' => $user,
        ]);
    }

    private static function getSelects()
    {
        return [
            'id',
            'name',
            'image',
            'email',
            'phone',
            'type',
        ];
    }
}