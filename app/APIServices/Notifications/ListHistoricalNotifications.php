<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ListHistoricalNotifications
{
    public static function execute($request)
    {
        $notifications = Notification::where('user_id',$request->user()->id)->get();

        return $notifications;
    }
}