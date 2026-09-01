<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Modifications\UpdateDay as UpdateService;

class UpdateDay
{
    public static function execute($day, $request)
    {
        $validated = $request->validate([
            'start_time' => 'nullable|date_format:H:i:s',
            'end_time' => 'nullable|date_format:H:i:s|after:start_time',
            'appointment_duration' => 'nullable|integer|min:1',
            'queue_length' => 'nullable|integer|min:0',
        ]);
        
        return UpdateService::execute($request->user(), $day, $validated);
    }
}   