<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\GetAvailableDays as GetAvailableDaysService;

class GetAvailableDays
{
    public static function execute($request)
    {
        $doctorId = $request->user()->clinicDoctorId();
        $limit = $request->filled('limit') ? (int) $request->input('limit') : null;

        return GetAvailableDaysService::execute($doctorId, $limit);
    }
}