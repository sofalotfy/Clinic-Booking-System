<?php

namespace App\Services\TemplateDays;

use App\Models\TemplateDay;

class CreateTemplateDay
{
    public static function execute($template_id, $day)
    {
        return TemplateDay::create([
            'template_plan_id' => $template_id,
            'day_of_week' => $day['day_of_week'],
            'start_time' => $day['start_time'],
            'end_time' => $day['end_time'],
            'appointment_duration' => $day['appointment_duration'],
            'queue_length' => $day['queue_length'],
        ]); 
    }
}