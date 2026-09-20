<?php

namespace App\APIServices\WhatsApp\Services;

use App\Models\TemplatePlan;
use App\Support\ArabicDateFormatter;

class FormatPLanToMessage
{
    public static function execute(?TemplatePlan $plan)
    {
        if (!$plan) {
            return "عذراً، لا يوجد خطة عمل مفعلة للطبيب حالياً.";
        }

        if ($plan->templateDays->isEmpty()) {
            return "خطة العمل المفعلة لا تحتوي على أيام عمل.";
        }

        $message = "خطة العمل الحالية:\n";

        $days = $plan->templateDays->sortBy('day_of_week');

        foreach ($days as $day) {
            $dayName = ArabicDateFormatter::getDayName($day->day_of_week);
            
            $startTimeString = substr($day->start_time, 0, 5);
            $endTimeString = substr($day->end_time, 0, 5);
            
            $startTime = ArabicDateFormatter::formatTime($startTimeString);
            $endTime = ArabicDateFormatter::formatTime($endTimeString);

            $message .= "• {$dayName}: من {$startTime} إلى {$endTime}\n";
        }

        return trim($message);
    }
}
