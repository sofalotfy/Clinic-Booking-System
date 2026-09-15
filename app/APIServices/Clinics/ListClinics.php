<?php

namespace App\APIServices\Clinics;

use App\Services\Clinics\ListClinics as ListService;

class ListClinics
{
    public static function execute($request)
    {
        $clinics = ListService::execute($request->user())
                ->select(self::getSelects())
                ->get();

        return $clinics;
    }

    private static function getSelects(): array
    {
        return [
            'clinics.id',
            'clinics.name',
            'clinics.location_link',
            'clinics.facebook',
            'clinics.instgram',
            'clinics.linkedin',
            'clinics.vezeeta',
        ];
    }
}
