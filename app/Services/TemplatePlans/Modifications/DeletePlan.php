<?php

namespace App\Services\TemplatePlans\Modifications;

use App\Services\TemplateDays\CreateTemplateDay;

class DeletePlan
{
    public static function execute($templatePlan)
    {
        foreach ($templatePlan->templateDays as $templateDay) {
            $templateDay->delete();
        }

        $templatePlan->delete();
        return true;
    }
}