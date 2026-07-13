<?php

namespace App\Services\DaysInstances;

use App\Models\Day;
use Carbon\Carbon;


class CreateDay
{
    public static function execute($template, $date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $templateDay = $template->templateDays()
            ->where('day_of_week', $dayOfWeek)
            ->first();

        if (!$templateDay) {
            return null;
        }


        
        return Day::create([
            'doctor_id' => $template->doctor_id,
            'date' => $date,
            'start_time' => $templateDay->start_time,
            'end_time' => $templateDay->end_time,
            'appointment_duration' => $templateDay->appointment_duration,
            'queue_length' => $templateDay->queue_length,
        ]); 
    }
}