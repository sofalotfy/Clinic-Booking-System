<?php

namespace App\Services\Roles;

use App\Models\User;
use App\Models\Role;

class DeleteRole
{
    public static function execute(User $user, Role $role)
    {
        abort_unless($role->isClinic(), 403, 'This is not a clinic role.');

        return $role->delete();
    }
}