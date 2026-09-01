<?php

namespace App\Services\Roles;

use App\Models\User;
use Spatie\Permission\Models\Role;

class DeleteRole
{
    public static function execute(User $user, Role $role)
    {
        return $role->delete();
    }
}