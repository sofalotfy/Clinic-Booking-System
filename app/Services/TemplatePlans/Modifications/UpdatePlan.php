<?php

namespace App\Services\TemplatePlans\Modifications;

use App\Services\TemplateDays\StoreTemplateDay;

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
        }

        return $templatePlan;
    }
}