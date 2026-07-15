<?php

namespace App\Services\TemplateDays;

use App\Models\TemplateDay;

class UpdateTemplateDay
{
    public static function execute(TemplateDay $templateDay, array $day): TemplateDay
    {
        $templateDay->update([
            'day_of_week' => $day['day_of_week'] ?? $templateDay->day_of_week,
            'start_time' => $day['start_time'] ?? $templateDay->start_time,
            'end_time' => $day['end_time'] ?? $templateDay->end_time,
            'appointment_duration' => $day['appointment_duration'] ?? $templateDay->appointment_duration,
            'queue_length' => $day['queue_length'] ?? $templateDay->queue_length,
        ]);

        return $templateDay->fresh();
    }
}