<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\GetAvailableSlots as GetAvailableSlotsService;

class GetAvailableSlots
{
    public static function execute($request, $day)
    {
        return GetAvailableSlotsService::execute($day->id);
    }
}