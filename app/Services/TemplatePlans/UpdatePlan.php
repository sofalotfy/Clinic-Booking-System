<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\UpdateTemplateDay;

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
            $requestDays = collect($days)->keyBy('day_of_week');

            foreach ($template->templateDays as $templateDay) {
                $day = $requestDays->get($templateDay->day_of_week);

                if ($day) {
                    UpdateTemplateDay::execute($templateDay, $day);
                }
            }
        }

        return $template->load('templateDays');
    }
}