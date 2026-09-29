<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use App\Services\Notifications\Handlers\Handler;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PatientDayAppointmentReminder extends Handler
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
        return $notification->body([
            'patient_name' => $model->patient->user->name,
            'date' => Carbon::parse($model->date),
        ]);
    }

    private static function buildWhatsAppParams(Model $model): array
    {
        $patientUser = $model->patient->user;

        return [
            'name' => $patientUser->name,
            'time' => ArabicDateFormatter::formatTime(
                Carbon::parse($model->date)->format('H:i')
            ),
        ];
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body, ?Notification $systemNotification = null)
    {
        return SendWhatsAppStatelessNotification::execute(
            $sender,
            $receiver,
            $clinicId,
            $notification->templateName(),
            self::buildWhatsAppParams($model)
        );
    }
}
