<?php

namespace App\Services\TemplatePlans\Checks;

use Carbon\Carbon;

class CheckSlotExistance
{
    public static function execute($day, $time): bool
    {
        $start = Carbon::parse($day->start_time);
        $end = Carbon::parse($day->end_time);
        $requestedTime = Carbon::parse($time);

        // Must be within working hours
        if ($requestedTime->lt($start) || $requestedTime->gte($end)) {
            return false;
        }

        // Check that the time falls exactly on a slot
        $minutesFromStart = $start->diffInMinutes($requestedTime);

        return ($minutesFromStart % $day->appointment_duration) === 0;
    }
}