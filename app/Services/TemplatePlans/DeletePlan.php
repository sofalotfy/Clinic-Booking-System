<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\CreateTemplateDay;

class DeletePlan
{
    public static function execute($plan_id)
    {
        $template = TemplatePlan::findOrFail($plan_id);

        foreach ($template->templateDays as $templateDay) {
            $templateDay->delete();
        }

        $template->delete();

        return true;
    }
}