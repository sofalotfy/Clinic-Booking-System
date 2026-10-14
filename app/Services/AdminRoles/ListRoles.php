<?php

namespace App\Services\AdminRoles;

use App\Models\Role;

class ListRoles
{
    public static function execute($filters = null)
    {
        return Role::query()
            ->whereNull('doctor_id')
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->when($filters, fn ($query) => self::filter($query, $filters));
    }

    private static function filter($builder, $filters)
    {
        return $builder;
    }
}
