<?php

namespace App\APIServices\Users;

use App\Models\User;

class GetUser
{
    public static function execute(int $userId)
    {
        $user = User::find($userId)->select();
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
