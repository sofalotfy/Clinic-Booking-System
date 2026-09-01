<?php

namespace App\Services\Roles;

use App\Models\User;
use Spatie\Permission\Models\Role;

class ListRoles
{
    public static function execute(User $user)
    {
        return Role::leftJoin('role_has_permissions','roles.id','=','role_has_permissions.role_id')
                ->leftJoin('permissions','role_has_permissions.permission_id','=','permissions.id')
                ->where('doctor_id', $user->clinicDoctorId())
                ->orderBy('roles.name', 'asc');
    }
}