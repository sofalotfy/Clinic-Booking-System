<?php

namespace App\Services\Permissions;

use App\Enums\UserType;
use Spatie\Permission\Models\Permission;

class GetUserPermissions
{
    public static function execute($user)
    {
        if ($user->type == UserType::DOCTOR) {
            return ListPermissions::execute($user);
        }

        return Permission::query()
        ->whereHas('roles', function ($query) use ($user) {
            $query->whereIn('roles.id', $user->roles()->select('roles.id'));
        })->orderBy('id');

    }
}