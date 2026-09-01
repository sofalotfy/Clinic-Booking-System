<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Retrievals\GetDayAppointments as GetDayAppointmentsService;

class GetDayAppointments
{
    public static function execute($request)
    {
        return GetDayAppointmentsService::execute($request->date, $request->user());
    }
}