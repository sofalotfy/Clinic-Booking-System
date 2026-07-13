<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;
use App\Services\DaysInstances\CreateDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ActivatatePlan
{
    public static function execute($template_id)
    {
        $template = TemplatePlan::findOrFail($template_id);

        $ActivePlan = TemplatePlan::where('doctor_id', $template->doctor_id)
            ->where('status', TemplatePlanStatus::ACTIVE)
            ->where('id', '!=', $template->id)
            ->first();

        $template->status = TemplatePlanStatus::ACTIVE;
        
        $template->save();

        if (!$ActivePlan) {
            $period = CarbonPeriod::create(
                Carbon::today(),
                Carbon::today()->addDays(30)
            );

            foreach ($period as $date) {
                CreateDay::execute($template, $date->toDateString());
            }
        }
        else{
            $ActivePlan->status = TemplatePlanStatus::IDLE;
        
            $ActivePlan->save();
        }
    }
}