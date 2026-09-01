<?php

namespace App\Services\Permissions;

use Spatie\Permission\Models\Role;

class GetUserPermissions
{
    public static function execute($user, $doctor)
    {
        return $user->roles()
            ->where('doctor_id', $doctor->id);
    }
}