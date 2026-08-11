<?php

namespace App\Services\Notifications\Doctor;

use App\Models\Doctor;
use App\Models\Notification;

class Notify
{
    public static function execute(Doctor $doctor, $type, $title, $text)
    {
        return Notification::create([
            'user_id' => $doctor->user_id,
            'type' => $type,
            'title' => $title,
            'text' => $text,
        ]);        
    }
}