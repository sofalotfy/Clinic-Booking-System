<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Notifications\WorkFlow\GetNotificationReceivers;
use Illuminate\Database\Eloquent\Model;

class NotificationManager
{
    public static function execute(User $sender, int $clinicId, $notification, Model $model)
    {
        $receivers = GetNotificationReceivers::execute($sender, $clinicId, $notification, $model);

        NotificationRouter::execute($sender, $clinicId, $notification, $receivers);

        return true;
    }
}