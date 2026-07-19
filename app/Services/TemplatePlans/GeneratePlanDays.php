<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Enums\TemplatePlanStatus;
use App\Services\DaysInstances\CreateDay;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GeneratePlanDays
{
    public static function execute($template)
    {
        $period = CarbonPeriod::create(
            Carbon::today(),
            Carbon::today()->addDays(30)
        );

        foreach ($period as $date) {
            CreateDay::execute($template, $date->toDateString());
        }

        return true;
    }
}