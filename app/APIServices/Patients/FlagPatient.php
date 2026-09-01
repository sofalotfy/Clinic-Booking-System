<?php

namespace App\APIServices\Patients;

use App\Models\Flag;
use App\Models\Patient;
use App\Services\Flags\FlagPatient as FlagService;

class FlagPatient
{
    public static function execute($request, Patient $patient, Flag $flag)
    {
        return FlagService::execute($request->user(), $flag, $patient);
    }
}