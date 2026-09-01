<?php

namespace App\APIServices\Days;

use Illuminate\Support\Facades\DB;
use App\Services\DaysInstances\Retrievals\ListDays as ListService;

class ListDays
{
    public static function execute($request)
    {
        $filters = [
                "status"    =>  $request->status,
                "date_from" =>  $request->date_from,
                "date_to"   =>  $request->date_to,
        ];
        return ListService::execute($request->user(), $filters)
            ->select(self::getSelects())
            ->get();
    }

    private static function getSelects()
    {
        return [
            'days.id',
            'days.date',
            'days.start_time',
            'days.end_time',
            'days.appointment_duration',
            'days.queue_length',
            DB::raw('COUNT(appointments.id) as total_appointments')
        ];
    }
}