<?php

namespace App\APIServices\Patients;

use App\Models\Flag;
use App\Models\Patient;
use App\Services\Flags\UnFlagPatient as UnFlagService;

class UnFlagPatient
{
    public static function execute($request, Patient $patient, Flag $flag)
    {
        $response = UnFlagService::execute($request->user(), $flag, $patient);

        return [
            'success' => $response,
        ];
    }
}