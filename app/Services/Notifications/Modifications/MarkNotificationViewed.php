<?php

namespace App\Services\Notifications\Modifications;

use App\Models\Notification;

class MarkNotificationViewed
{
    /**
     * The stateful WhatsApp channel stores the id of the in-app notification it
     * sent in the conversation data. Once the patient answers a quick reply the
     * notification is resolved and must not stay unread in the app.
     */
    public static function execute(?int $notificationId): ?Notification
    {
        if (! $notificationId) {
            return null;
        }

        $notification = Notification::find($notificationId);

        if (! $notification) {
            return null;
        }

        return $notification->markViewed();
    }
}
