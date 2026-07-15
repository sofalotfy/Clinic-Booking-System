<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\CreateTemplateDay;

class UpdatePlan
{
    public static function execute($name, $description, $days, $plan_id)
    {
        $template = TemplatePlan::findOrFail($plan_id);

        $template->update([
            'name' => $name ?? $template->name,
            'description' => $description ?? $template->description,
        ]);

        if ($days) {
            foreach ($days as $day) {
                CreateTemplateDay::execute($template->id, $day);
            }

            foreach ($template->templateDays as $templateDay) {
                $templateDay->delete();
            }
        }

        return $template->load('templateDays');
    }
}