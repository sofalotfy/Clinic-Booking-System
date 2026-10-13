<?php

namespace App\AdminServices\Admins;

use App\Models\User;
use App\Services\Admins\ShowAdmin as ShowAdminService;

class ShowAdmin
{
    public static function execute(User $admin)
    {
        $admin = ShowAdminService::execute($admin);

        return [
            'id' => $admin->id,
            'name' => $admin->name,
            'email' => $admin->email,
            'phone' => $admin->phone,
            'age' => $admin->age,
            'gender' => $admin->gender?->value,
            'area' => $admin->area,
            'image' => $admin->image,
            'role_ids' => $admin->roles->pluck('id')->values()->all(),
            'roles' => $admin->roles->pluck('name')->values(),
            'created_at' => $admin->created_at?->format('Y-m-d'),
        ];
    }
}
