<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ViewNotification
{
    public static function execute($request, Notification $notification)
    {
        if ($notification->receiver_id !== $request->user()->id) {
            abort('403', 'You are not authorized to view this notification!');
        }

        return $notification->markViewed();
    }
}
