<?php

namespace App\Services\AdminRoles;

use App\Models\Role;

class ShowRole
{
    public static function execute(Role $role)
    {
        abort_unless($role->isAdmin(), 403, 'This is not an admin role.');

        return $role->load('permissions');
    }
}
