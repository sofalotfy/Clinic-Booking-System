<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\CreateTemplateDay;

class UpdatePlan
{
    public static function execute($name, $description, $days, $plan_id)
    {
        $template = TemplatePLan::find($plan_id);
        
        $template::update([
            'name'      => $name,
            'description' => $description,
        ]);

        foreach ($template->templateDays as $index => $templateDay) {
            UpdateTemplateDay::execute($templateDay, $days[$index]);
        }

        return $template->with('templateDays');
    }
}