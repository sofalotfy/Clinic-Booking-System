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

class PatientAppointmentBooked extends Handler
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
        $dateTime = Carbon::parse($model->date)->format('M j, Y g:i A');

        return $notification->body([
            'patient_name' => $model->patient->user->name,
            'date' => $dateTime,
        ]);
    }

    private static function buildWhatsAppParams(Model $model): array
    {
        $patientUser = $model->patient->user;

        return [
            'name' => $patientUser->name,
            'phone' => $patientUser->phone,
            'date' => ArabicDateFormatter::format(
                Carbon::parse($model->date)
            ),
        ];
    }

    protected static function sendWhatsApp(User $sender, array $receiver, int $clinicId, $notification, $model, string $title, string $body, ?Notification $systemNotification = null)
    {
        return SendWhatsAppStatelessNotification::execute(
            $sender,
            $receiver['phone'],
            $clinicId,
            $notification->templateName(),   // template comes from the notification
            self::buildWhatsAppParams($model)
        );
    }
}
