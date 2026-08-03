<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ListNewNotifications
{
    public static function execute($request)
    {
        $notifications = Notification::where('user_id',$request->user()->id)->where('viewed',false)->get();

        return $notifications;
    }
}