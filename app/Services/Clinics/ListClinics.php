<?php

namespace App\Services\Clinics;

use App\Models\Clinic;

class ListClinics
{
    public static function execute($user, $filters = null)
    {
        return Clinic::query()
            ->where('doctor_id', $user->clinicDoctorId())
            ->when($filters, fn ($query) => self::filter($query, $filters));
    }

    private static function filter($builder, $filters)
    {
        // Add specific search filters if necessary
        return $builder;
    }
}
