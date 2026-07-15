<?php

namespace App\Services\TemplatePlans;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\CreateTemplateDay;

class CreteaTemplate
{
    public static function execute($name, $description, $days)
    {
        $template = TemplatePlan::create([
            'doctor_id' => auth()->user()->doctor->id,
            'name'      => $name,
            'description' => $description,
        ]);

        // foreach ($days as $day) {
        //     CreateTemplateDay::execute($template->id, $day);
        // }

        ActivatatePlan::execute($template->id);

        return $days;
    }
}