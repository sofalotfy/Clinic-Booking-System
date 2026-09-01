<?php

namespace App\Services\DaysInstances\Retrievals;

use Carbon\Carbon;
use App\Models\Day;
use Illuminate\Support\Facades\DB;
use App\Enums\AppointmentStatus;

class ShowDay
{
    public static function execute($day)
    {
        $day = Day::select(self::getSelects())
                ->where('id', $day->id)->first();

        $day->appointments_count = DB::table('appointments')
            ->whereIn('status', AppointmentStatus::working())
            ->whereDate('appointments.date', $day->date)
            ->where('appointments.doctor_id', $day->doctor_id)
            ->count();

        return $day;
    }

    private static function getSelects()
    {
        return [
            'id',
            'date',
            'start_time',
            'end_time',
            'appointment_duration',
            'queue_length',
        ];
    }
}