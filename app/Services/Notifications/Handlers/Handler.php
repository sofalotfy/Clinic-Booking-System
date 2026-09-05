<?php

namespace App\Services\Notifications\Handlers;

use App\Models\User;
use App\Services\Notifications\Channels\PushSystemNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class Handler
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        static::dispatch($sender, $clinicId, $notification, $receivers, '', '');
    }

    protected static function dispatch(User $sender, int $clinicId, $notification, Collection $receivers, $model, string $title, string $body)
    {
        \Log::info("in handler receivers " . $receivers->toJson());
        foreach ($receivers as $receiver) {
            \Log::info("in handler iteration for {$receiver->name}");
            
            PushSystemNotification::execute($sender, $receiver, $clinicId, $title, $body, $notification->link());

            static::sendWhatsApp($sender, $receiver, $clinicId, $notification, $model, $title, $body);
        }
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body)
    {
        //
    }
}