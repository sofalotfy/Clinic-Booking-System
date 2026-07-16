<?php

namespace App\APIServices\Days;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetDays
{
    public static function execute()
    {
        $days = Day::select(self::getSelects())
                    ->leftJoin('appointments',function($join){
                        $join->on(DB::raw('DATE(appointments.date)'), '=', 'days.date');
                    })
                    ->groupBy('days.id')
                    ->orderBy('days.date', 'desc')
                    ->get();
                    
        return $days;
    }

    private static function getSelects()
    {
        return [
            'days.id',
            'days.date',
            'days.start_time',
            'days.end_time',
            'days.appointment_duration',
            DB::raw('COUNT(appointments.id) as total_appointments')
        ];
    }
}