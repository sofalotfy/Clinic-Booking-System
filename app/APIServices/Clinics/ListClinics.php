<?php

namespace App\APIServices\Clinics;

use App\Services\Clinics\ListClinics as ListService;

class ListClinics
{
    public static function execute($request)
    {
        $clinics = ListService::execute($request->user())
            ->select(self::getSelects())
            ->addSelect([
                'description' => Doctor::select('description')
                    ->whereColumn('doctors.id', 'clinics.doctor_id')
                    ->limit(1),
            ])
            ->get();

        return $clinics;
    }

    private static function getSelects(): array
    {
        return [
            'clinics.id',
            'clinics.name',
            'clinics.location_link',
            'clinics.address',
            'clinics.facebook',
            'clinics.instgram',
            'clinics.linkedin',
            'clinics.vezeeta',
            'clinics.clinic_phone',
            'clinics.notifications_phone',
        ];
    }
}
