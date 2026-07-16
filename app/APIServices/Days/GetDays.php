<?php

namespace App\APIServices\Days;

use App\Models\Appointment;
use App\Models\Day;
use App\Enums\AppointmentStatus;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class GetDays
{
    public static function execute($filters = null)
    {
        $days = Day::select(self::getSelects())
                    ->leftJoin('appointments',function($join) use ($filters){
                        $join->on(DB::raw('DATE(appointments.date)'), '=', 'days.date')
                            ->where('appointments.doctor_id', auth()->user()->doctor->id);
                    });

        $days = self::filter($days, $filters);

        $days = $days->where('days.doctor_id', auth()->user()->doctor->id)
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

    private static function filter($builder, $filters)
    {
        return $builder
            ->when(isset($filters['status']) && is_array($filters['status']), function ($query) use ($filters) {
                $query->whereIn('appointments.status', $filters['status']);
            })
            ->when(isset($filters['from']) && isset($filters['to']), function ($query) use ($filters) {
                $query->whereBetween('days.date', [$filters['from'], $filters['to']]);
            });
    }
}