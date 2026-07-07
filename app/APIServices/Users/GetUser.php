<?php

namespace App\APIServices\Users;

use App\Models\User;

class GetUser
{
    public static function execute(int $userId)
    {
        $user = User::where('id', $userId)->select(self::getSelects())->first();
        $user->image = $user->image ? asset('storage/' . $user->image) : null;
        return $user;
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
