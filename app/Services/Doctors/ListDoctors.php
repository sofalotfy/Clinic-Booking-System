<?php

namespace App\Services\Doctors;

use App\Models\Doctor;

class ListDoctors
{
    public static function execute($filters = null)
    {
        return Doctor::query()
            ->with(['user', 'clinic', 'whatsappAccount'])
            ->latest()
            ->when($filters, fn ($query) => self::filter($query, $filters));
    }

    private static function filter($builder, $filters)
    {
        return $builder;
    }
}
