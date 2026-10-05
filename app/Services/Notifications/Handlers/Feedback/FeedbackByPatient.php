<?php

namespace App\Services\Notifications\Handlers\Feedback;

use App\Models\Doctor;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use App\Services\Notifications\Handlers\Handler;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class FeedbackByPatient extends Handler
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        $title = static::buildTitle($model, $notification);
        $body = static::buildBody($model, $notification);

        static::dispatch($sender, $clinicId, $notification, $receivers, $model, $title, $body);
    }

    private static function buildTitle(Model $model, $notification): string
    {
        return $notification->title();
    }

    private static function buildBody(Model $model, $notification): string
    {
        return $notification->body(self::buildData($model));
    }

    /**
     * The template and the in-app notification body read from the same fields.
     */
    private static function buildData(Model $model): array
    {
        $doctor = Doctor::find($model->doctor_id);
        $patient = User::find($model->user_id);

        return [
            'doctor' => $doctor?->user?->name,
            'patient' => $patient?->name,
            'message' => $model->message,
            'phone' => $patient?->phone,
        ];
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body, ?Notification $systemNotification = null)
    {
        return SendWhatsAppStatelessNotification::execute(
            $sender,
            $receiver,
            $clinicId,
            $notification->templateName(),   // template comes from the notification
            self::buildData($model)
        );
    }
}