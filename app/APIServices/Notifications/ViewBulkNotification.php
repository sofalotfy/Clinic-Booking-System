<?php

namespace App\APIServices\Notifications;

use App\Models\Notification;

class ViewBulkNotification
{
    public static function execute($request)
    {
        $ids = $request->input('ids');

        if (! is_array($ids) || $ids === []) {
            abort(400, 'Missing notification IDs!');
        }

        Notification::forUser($request->user())->whereIn('id', $ids)->update(['viewed' => true]);

        $notifications = Notification::forUser($request->user())->whereIn('id', $ids)->get();

        // Scoped to the receiver, so this also covers ids belonging to other users.
        if ($notifications->isEmpty()) {
            abort(404, 'No notifications found!');
        }

        return $notifications;
    }
}
