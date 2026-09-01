<?php

namespace App\Services\Permissions;

use Spatie\Permission\Models\Permission;

class ListPermissions
{
    public static function execute($user, $filters)
    {
        return Permission::when($filters, fn ($builder) => self::filter($builder, $filters))
            ->orderBy('id', 'asc');
    }

    private static function filter($builder, $filters)
    {
        return $builder
            ->when(isset($filters['type']), function ($builder) use ($filters) {
                $builder->where('type', $filters['type']);
            });
    }
}