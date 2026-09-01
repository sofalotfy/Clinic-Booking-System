<?php

namespace App\Services\Flags;

use App\Models\Flag;

class ListFlags
{
    public static function execute($user, $filters = null)
    {
        return Flag::query()
            ->where('doctor_id', $user->clinicDoctorId())
            ->when($filters, fn ($query) => self::filter($query, $filters));
    }

    private static function filter($builder, $filters)
    {
        return $builder
            ->when( isset($filters['name']), function ($query) use ($filters) {
                    $query->where('flags.name', 'like', '%' . $filters['name'] . '%');
                }
            )
            ->when( isset($filters['color']), function ($query) use ($filters) {
                    $query->where('flags.color', $filters['color']);
                }
            );
    }
}