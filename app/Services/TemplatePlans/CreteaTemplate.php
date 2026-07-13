<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\CreateTemplateDay;

class CreteaTemplate
{
    public static function execute($doctor_id, $days)
    {
        $template = TemplatePlan::create([
            'doctor_id' => $doctor_id,
        ]);

        foreach ($days as $day) {
            CreateTemplateDay::execute($template->id, $day);
        }

        ActivatatePlan::execute($template->id);
    }
}