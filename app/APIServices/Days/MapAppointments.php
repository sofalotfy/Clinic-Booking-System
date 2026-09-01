<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\MapAppointments as MappingService;

class MapAppointments
{
    public static function execute($request)
    {
        return MappingService::execute($request->user());
    }
}