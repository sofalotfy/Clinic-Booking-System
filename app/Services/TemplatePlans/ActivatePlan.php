<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;
use App\Services\DaysInstances\CreateDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class ActivatePlan
{
    public static function execute($template_id)
    {
        $template = TemplatePlan::with('templateDays')->findOrFail($template_id);

        $ActivePlan = TemplatePlan::where('doctor_id', $template->doctor_id)
            ->where('status', TemplatePlanStatus::ACTIVE)
            ->where('id', '!=', $template->id)
            ->first();

        $template->update([
            'status' => TemplatePlanStatus::ACTIVE,
        ]);

        if ($ActivePlan) {
            $ActivePlan->update([
                'status' => TemplatePlanStatus::IDLE,
            ]);
        }

        $period = CarbonPeriod::create(
            Carbon::today(),
            Carbon::today()->addDays(30)
        );

        foreach ($period as $date) {
            CreateDay::execute($template, $date->toDateString());
        }
    }
}