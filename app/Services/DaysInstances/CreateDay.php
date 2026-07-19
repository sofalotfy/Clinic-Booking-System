<?php

namespace App\Services\DaysInstances;

use App\Models\Day;
use App\Models\Appointment;
use Carbon\Carbon;


class CreateDay
{
    public static function execute($template, $date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        $templateDay = $template->templateDays
            ->firstWhere('day_of_week', $dayOfWeek);

        if (!$templateDay) {
            return null;
        }

        if (Appointment::whereDate('date', $date)->where('doctor_id', $template->doctor_id)->exists()) {
            return null;
        }

        if ($day = Day::where('date', $date)->where('doctor_id', $template->doctor_id)->first()) {
            $day->delete();
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