<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use App\Services\Notifications\Handlers\Handler;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DoctorAppointmentRescheduled extends Handler
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
        $format = $model->status == AppointmentStatus::QUEUED ? 'M j, Y' : 'M j, Y g:i A';

        $fromDate = $model->old_date
            ? Carbon::parse($model->old_date)->format($format)
            : '-';
        $toDate = Carbon::parse($model->date)->format($format);

        return $notification->body([
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    private static function buildWhatsAppParams(array $receiver, Model $model): array
    {
        $includeTime = $model->status != AppointmentStatus::QUEUED;

        $format = fn ($raw) => ArabicDateFormatter::format(
            Carbon::parse($raw),
            includeTime: $includeTime
        );

        return [
            'name' => $receiver['name'] ?? '',
            'date_from' => $model->old_date ? $format($model->old_date) : '-',
            'date_to' => $format($model->date),
        ];
    }

    protected static function sendWhatsApp(User $sender, array $receiver, int $clinicId, $notification, $model, string $title, string $body, ?Notification $systemNotification = null)
    {
        return SendWhatsAppStatelessNotification::execute(
            $sender,
            $receiver['phone'],
            $clinicId,
            $notification->templateName(),
            self::buildWhatsAppParams($receiver, $model),
            SendWhatsAppStatelessNotification::ENGLISH_LANGUAGE
        );
    }
}
