<?php

namespace App\APIServices\Users;

use App\Models\User;

class GetUser
{
    public static function execute(int $userId)
    {
        return User::where('id', $userId)->select($this->getSelects())->first();
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
