<?php

namespace App\APIServices\PatientBlocks;

use App\Services\PatientBlocks\ListBlockedPatients as ListService;

class ListBlockedPatients
{
    public static function execute($request)
    {
        return ListService::execute($request->user())
            ->select(self::getSelects())
            ->get();   
    }

    private static function getSelects(): array
    {
        return [
            'patients.id as patient_id',

            'users.name as patient_name',
            'users.phone as patient_phone',
            'users.email as patient_email',
            'users.image as patient_avatar',
            'users.age as patient_age',
            'users.area as patient_area',

            'patient_blocks.id as block_id',
            'patient_blocks.blocked_by',
            'patient_blocks.reason',
            'patient_blocks.blocked_at',
            'patient_blocks.expires_at',
        ];
    }
}