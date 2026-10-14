<?php

namespace App\Services\Admins;

use App\Models\User;

class ShowAdmin
{
    public static function execute(User $admin)
    {
        return $admin->load('roles');
    }
}
