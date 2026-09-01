<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\ShowDay as ShowService;

class ShowDay
{
    public static function execute($request, $day)
    {
        return ShowService::execute($day);
    }
}   