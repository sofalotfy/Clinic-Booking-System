<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Modifications\CancelDay as CancelDayService;

class CancelDay
{
    public static function execute($user, $day)
    {
        CancelDayService::execute($user, $day);
    }
}
