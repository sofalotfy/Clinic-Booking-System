<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\GetEmptyDays as GetEmptyDaysService;

class GetEmptyDays
{
    public static function execute($request)
    {
        return GetEmptyDaysService::execute($request->user(), $request->user()->clinicDoctorId());
    }
}