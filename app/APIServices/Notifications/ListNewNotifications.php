<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ListNewNotifications
{
    public static function execute($request)
    {
        return Notification::forUser($request->user())
            ->unread()
            ->latest()
            ->get();
    }
}
