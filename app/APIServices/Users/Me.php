<?php

namespace App\APIServices\Users;

class Me
{
    public static function execute($request)
    {
        return ShowUser::execute($request, $request->user());
    }
}
