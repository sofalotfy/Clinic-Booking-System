<?php

namespace App\Services\DaysInstances\Retrievals;

use App\Models\Day;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MapAppointments
{
    public static function execute($user = null)
    {
        $user = $user ?? auth()->user();

        $startDate = Carbon::today()->toDateString();
        $doctorId = $user->clinicDoctorId();

        return Day::select(self::getSelects())
            ->leftJoin('appointments', function ($join) use ($doctorId) {
                $join->on(
                    DB::raw('DATE(appointments.date)'),
                    '=',
                    'days.date'
                );

                $join->where('appointments.doctor_id', $doctorId);
            })
            ->where('days.date', '>=', $startDate)
            ->where('days.doctor_id', $doctorId)
            ->groupBy('days.date', 'days.id')
            ->get();
    }

    private static function getSelects()
    {
        return [
            DB::raw('COUNT(appointments.id) as total_appointments'),
            'days.id as day_id',
            'days.date',
        ];
    }
}