<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Models\Notification;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use App\Services\Notifications\Handlers\Handler;
use App\Support\ArabicDateFormatter;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class DoctorDayAppointments extends Handler
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
     * The four template variables, mirroring the admin menu "today appointments"
     * option: the doctor's name, the confirmed appointments count, the still
     * available slots and the first appointment time.
     */
    private static function buildData(Model $model): array
    {
        $appointments = $model->getAppointments();

        return [
            'name' => $model->doctor->user->name,
            'confirmed' => $appointments->count(),
            'available' => $model->getEmptySlots($appointments->count()),
            'time' => $model->getFirstAppointmentTime($appointments),
        ];
    }

    private static function buildWhatsAppParams(Model $model): array
    {
        $data = self::buildData($model);

        return [
            'name' => $data['name'],
            'confirmed' => ArabicDateFormatter::toArabicDigits($data['confirmed']),
            'available' => ArabicDateFormatter::toArabicDigits($data['available']),
            'time' => $data['time'],
        ];
    }

    protected static function sendWhatsApp(User $sender, array $receiver, int $clinicId, $notification, $model, string $title, string $body, ?Notification $systemNotification = null)
    {
        return SendWhatsAppStatelessNotification::execute(
            $sender,
            $receiver['phone'],
            $clinicId,
            $notification->templateName(),
            self::buildWhatsAppParams($model),
            SendWhatsAppStatelessNotification::ENGLISH_LANGUAGE
        );
    }
}
