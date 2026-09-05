<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Notifications\Handlers\Handler;
use Illuminate\Support\Collection;

class NotificationRouter
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        $handler = match ($notification->type()) {
            default => Handler::class,
        };

        $handler::execute($sender, $clinicId, $notification, $receivers, $model);
    }
}