<?php

namespace App\Services\DaysInstances\Creations;

use App\Models\Day;
use App\Models\TemplatePlan;
use Carbon\Carbon;

class StoreDay
{
    public static function execute($user, TemplatePlan $templatePlan, string $date): ?Day {
        // Don't create the day if it already exists for this doctor.
        $existingDay = Day::where('doctor_id', $templatePlan->doctor_id)
            ->whereDate('date', $date)
            ->first();

        if ($existingDay) {
            return null;
        }

        // Find the template day for this date.
        $templateDay = self::findTemplateDay($templatePlan, $date);

        // Don't create anything if this day of the week
        // isn't configured in the template plan.
        if (!$templateDay) {
            return null;
        }

        return Day::create([
            'doctor_id' => $templatePlan->doctor_id,
            'date' => $date,
            'start_time' => $templateDay->start_time,
            'end_time' => $templateDay->end_time,
            'appointment_duration' => $templateDay->appointment_duration,
            'queue_length' => $templateDay->queue_length,
        ]);
    }

    protected static function findTemplateDay(TemplatePlan $templatePlan, string $date) {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return $templatePlan->templateDays
            ->firstWhere('day_of_week', $dayOfWeek);
    }
}