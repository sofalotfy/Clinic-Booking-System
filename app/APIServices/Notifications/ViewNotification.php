<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ViewNotification
{
    public static function execute($request, $notification)
    {
        if($notification->user_id != $request->user()->id){
            abort('403','You are not authorized to view this notification!');
        }

        $notification->update([
            "viewed" => true,
        ]);
        
        return $notification;
    }
}   