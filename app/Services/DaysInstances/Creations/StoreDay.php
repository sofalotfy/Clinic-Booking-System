<?php

namespace App\Services\DaysInstances\Creations;

use App\Models\Day;
use App\Models\TemplatePlan;
use Carbon\Carbon;

class StoreDay
{
    public static function execute($user, TemplatePlan $templatePlan, string $date): Day
    {
        $templateDay = self::findTemplateDay($templatePlan, $date);

        return Day::create([
            'doctor_id' => $templatePlan->doctor_id,
            'date' => $date,
            'start_time' => $templateDay->start_time,
            'end_time' => $templateDay->end_time,
            'appointment_duration' => $templateDay->appointment_duration,
            'queue_length' => $templateDay->queue_length,
        ]);
    }

    protected static function findTemplateDay(TemplatePlan $templatePlan, string $date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return $templatePlan->templateDays->firstWhere('day_of_week', $dayOfWeek);
    }
}