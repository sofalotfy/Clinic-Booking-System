<?php

namespace App\Services\DaysInstances\Modifications;

use Carbon\Carbon;

class SlotGenerator
{
    /**
     * Generate the sequence of appointment start times for a day,
     * based on its start/end time and appointment duration.
     *
     * @param  object  $day  Must expose date, start_time, end_time, appointment_duration
     * @return Carbon[]
     */
    public static function generate($day): array
    {
        $slots = [];

        $current = Carbon::parse("{$day->date} {$day->start_time}");
        $end = Carbon::parse("{$day->date} {$day->end_time}");

        while ($current->copy()->addMinutes($day->appointment_duration)->lte($end)) {
            $slots[] = $current->copy();
            $current->addMinutes($day->appointment_duration);
        }

        return $slots;
    }
}