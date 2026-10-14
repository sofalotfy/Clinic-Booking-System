<?php

namespace App\AdminServices\Admins;

use App\Services\Admins\ListAdmins as ListAdminsService;

class ListAdmins
{
    public static function execute($request)
    {
        return ListAdminsService::execute()
            ->paginate(15)
            ->through(fn ($admin) => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
                'gender' => $admin->gender?->value,
                'area' => $admin->area,
                'image' => $admin->image,
                'roles' => $admin->roles->pluck('name')->values(),
                'joined' => $admin->created_at?->format('Y-m-d'),
            ]);
    }
}
