<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ViewBulkNotification
{
    public static function execute($request)
    {
        if(!isset($request->ids)){
            abort(400, 'Missing notification IDs!');
        }

        $notifications = Notification::where('user_id',$request->user()->id)->whereIn('id', $request->ids)->update([
            "viewed" => true,
        ]);

        if($notifications == 0){
            abort(404, 'No notifications found!');
        }

        $notifications = Notification::where('user_id', $request->user()->id)
            ->whereIn('id', $request->ids)
            ->get();

        return $notifications;
    }
}