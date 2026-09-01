<?php

namespace App\Services\TemplatePlans\Modifications;

use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;
use App\Services\DaysInstances\Modifications\ManageDaySynchronization;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ActivatePlan
{
    public static function execute($user, $templatePlan, $date)
    {
        $ActivePlan = TemplatePlan::where('doctor_id', $templatePlan->doctor_id)
            ->where('status', TemplatePlanStatus::ACTIVE)
            ->where('id', '!=', $templatePlan->id)
            ->first();

        $templatePlan->update([
            'status' => TemplatePlanStatus::ACTIVE,
        ]);

        if ($ActivePlan) {
            $ActivePlan->update([
                'status' => TemplatePlanStatus::IDLE,
            ]);
        }

        $startDate = Carbon::parse($date);

        $period = CarbonPeriod::create(
            $startDate,
            $startDate->copy()->addDays(30)
        );

        foreach ($period as $date) {
            ManageDaySynchronization::execute($user, $templatePlan, $date->toDateString(), true);
        }
        
        return true;
    }
}