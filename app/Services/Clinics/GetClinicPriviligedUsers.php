<?php

namespace App\Services\Clinics;

use App\Models\Role;
use App\Models\User;

class GetClinicPriviligedUsers
{
    public static function execute(int $clinicId, string $permission)
    {
        $roleIds = Role::where('doctor_id', $clinicId)
            ->whereHas('permissions', fn ($query) => $query->where('name', $permission))
            ->pluck('id');

        return User::whereHas('roles', fn ($query) => $query->whereIn('roles.id', $roleIds))
            ->get();
    }
}