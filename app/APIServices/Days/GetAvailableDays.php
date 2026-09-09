<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\GetAvailableDays as GetAvailableDaysService;

class GetAvailableDays
{
    public static function execute($request)
    {
        $doctorId = $request->user()->clinicDoctorId();
            
        return GetAvailableDaysService::execute($doctorId);
    }
}