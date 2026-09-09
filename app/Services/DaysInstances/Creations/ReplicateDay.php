<?php

namespace App\Services\DaysInstances\Creations;

use App\Models\Day;
use App\Models\TemplatePlan;
use Carbon\Carbon;
use App\Models\User;

class ReplicateDay
{
    public static function execute(User $user, Day $day, $date = null)
    {
        return Day::create([
            'doctor_id' => $day->doctor_id,
            'date' => $date ?? $day->date,
            'start_time' => $day->start_time,
            'end_time' => $day->end_time,
            'appointment_duration' => $day->appointment_duration,
            'queue_length' => $day->queue_length,
        ]);
    }
}