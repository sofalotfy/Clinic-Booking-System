<?php

namespace App\Services\TemplatePlans\Modifications;

use App\Enums\TemplatePlanStatus;
use App\Services\DaysInstances\Modifications\ManageDaySynchronization;
use App\Services\TemplateDays\StoreTemplateDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class UpdatePlan
{
    public static function execute($user, $templatePlan, $name, $description, $days)
    {
        $templatePlan->update([
            'name' => $name ?? $templatePlan->name,
            'description' => $description ?? $templatePlan->description,
        ]);

        if ($days) {
            foreach ($templatePlan->templateDays as $templateDay) {
                $templateDay->delete();
            }

            foreach ($days as $day) {
                StoreTemplateDay::execute($user, $templatePlan, $day);
            }

            static::syncActivePlanDays($user, $templatePlan);
        }

        return $templatePlan;
    }

    /**
     * When the edited plan is the active one, re-sync the generated calendar
     * days against the new template so the edits take effect on live days.
     */
    protected static function syncActivePlanDays($user, $templatePlan): void
    {
        if ($templatePlan->status !== TemplatePlanStatus::ACTIVE) {
            return;
        }

        $templatePlan->unsetRelation('templateDays');

        $startDate = Carbon::today();

        $period = CarbonPeriod::create(
            $startDate,
            $startDate->copy()->addDays(30)
        );

        foreach ($period as $date) {
            ManageDaySynchronization::execute($user, $templatePlan, $date->toDateString(), true);
        }
    }
}
