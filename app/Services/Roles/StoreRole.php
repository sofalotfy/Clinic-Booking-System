<?php

namespace App\Services\Roles;

use App\Models\User;
use Spatie\Permission\Models\Role;

class StoreRole
{
    public static function execute(User $user, string $roleName, array $permissions)
    {
        $role = Role::create([
            'name' => $roleName,
            'guard_name' => 'web',
            'doctor_id' => $user->clinicDoctorId(),
        ]);

        $role->syncPermissions($permissions);

        return $role;
    }
}