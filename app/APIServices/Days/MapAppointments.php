<?php

namespace App\APIServices\Days;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MapAppointments
{
    public static function execute()
    {
        $start_date = Carbon::now()->format('y-m-d');

        $days = Day::select(self::getSelects())
            ->leftJoin('appointments',function($join){
                        $join->on(DB::raw('DATE(appointments.date)'), '=', 'days.date');
                    })
            ->where('days.date','>=',$start_date)
            ->groupBy('days.date')
            ->get();
                    
        return $days;
    }

    private static function getSelects()
    {
        return [
            DB::raw('COUNT(appointments.id) as total_appointments'),
            "days.id as day_id",
            "days.date",
        ];
    }
}