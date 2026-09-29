<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ListHistoricalNotifications
{
    public static function execute($request)
    {
        return Notification::forUser($request->user())
            ->latest()
            ->get();
    }
}
