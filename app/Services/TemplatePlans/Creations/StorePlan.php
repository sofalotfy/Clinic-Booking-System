<?php

namespace App\Services\TemplatePlans\Creations;

use App\Models\TemplatePlan;
use App\Services\TemplateDays\StoreTemplateDay;

class StorePlan
{
    public static function execute($user, $name, $description, $days)
    {
        $template = TemplatePlan::create([
            'doctor_id' => $user->clinicDoctorId(),
            'name' => $name,
            'description' => $description,
        ]);
        
        foreach ($days as $day) {
            StoreTemplateDay::execute($user, $template, $day);
        }

        return $template->refresh();
    }
}