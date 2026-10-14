<?php

namespace App\Services\Admins;

use App\Enums\UserType;
use App\Models\User;

class ListAdmins
{
    public static function execute($filters = null)
    {
        return User::query()
            ->where('type', UserType::ADMIN)
            ->with('roles')
            ->latest()
            ->when($filters, fn ($query) => self::filter($query, $filters));
    }

    private static function filter($builder, $filters)
    {
        return $builder;
    }
}
