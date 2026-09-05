<?php

namespace App\Services\Notifications\Channels;

use App\Models\Notification;
use App\Models\User;

class PushSystemNotification
{
    public static function execute(User $sender, User $receiver, int $clinicId, string $title, string $body, string $link)
    {
        return Notification::create([
            'sender_id' => $sender->id,
            'receiver_id' => $receiver->id,
            'doctor_id' => $clinicId,
            'title' => $title,
            'text' => $body,
            'route' => $link,
        ]);
    }
}