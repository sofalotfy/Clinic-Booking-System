<?php

namespace App\Services\Roles;

use App\Models\User;
use Spatie\Permission\Models\Role;

class ShowRoles
{
    public static function execute(User $user, Role $role)
    {
        return Role::where('doctor_id', $user->clinicDoctorId())
            ->where('roles.id', $role->id)
            ->leftJoin('role_has_permissions', 'roles.id', '=', 'role_has_permissions.role_id')
            ->leftJoin('permissions', 'role_has_permissions.permission_id', '=', 'permissions.id');
    }
}