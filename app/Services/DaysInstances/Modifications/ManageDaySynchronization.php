<?php

namespace App\Services\DaysInstances\Modifications;

use App\Models\Day;
use App\Models\TemplatePlan;
use Carbon\Carbon;
use App\Services\DaysInstances\Creations\StoreDay;

class ManageDaySynchronization
{
    /**
     * Sync a single calendar day with its template plan.
     *
     * - If the template no longer defines a day for this weekday:
     *      - if $force is true, the existing Day is deleted (and its appointments cancelled).
     *      - if $force is false, nothing is changed (day and appointments are left as-is).
     * - If the template defines a day, the old Day (if any) is deleted (and its appointments
     *   cancelled) and a new one is generated and rescheduled, regardless of $force.
     */
    public static function execute($user, TemplatePlan $templatePlan, string $date, bool $force = false): ?Day
    {
        $templateDay = static::findTemplateDay($templatePlan, $date);
        $presentDay = static::findExistingDay($templatePlan, $date);

        if (!$templateDay) {
            if ($force && $presentDay) {
                DeleteDay::execute($user, $presentDay);
            }

            return null;
        }

        if ($presentDay) {
            DeleteDay::execute($user, $presentDay, false);
        }

        $day = StoreDay::execute($user, $templatePlan, $date);

        SyncDayAppointments::execute($user, $day);

        return $day;
    }

    /**
     * Find the template day definition matching the given date's weekday.
     */
    protected static function findTemplateDay(TemplatePlan $templatePlan, string $date)
    {
        $dayOfWeek = Carbon::parse($date)->dayOfWeek;

        return $templatePlan->templateDays->firstWhere('day_of_week', $dayOfWeek);
    }

    /**
     * Find the currently existing Day record for this doctor/date, if any.
     */
    protected static function findExistingDay(TemplatePlan $templatePlan, string $date): ?Day
    {
        return Day::where('date', $date)
            ->where('doctor_id', $templatePlan->doctor_id)
            ->first();
    }
}