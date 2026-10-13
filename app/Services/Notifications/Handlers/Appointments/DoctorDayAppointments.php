<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Models\Notification;
use App\Models\User;
use App\Services\Appointments\Retrievals\ListAppointments;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use App\Services\Notifications\Handlers\Handler;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
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
        $appointments = ListAppointments::execute($model->doctor->user, [
            'date_from' => $model->date,
            'date_to' => $model->date,
        ])
            ->active()
            ->orderBy('date', 'asc')
            ->get();

        return [
            'name' => $model->doctor->user->name,
            'confirmed' => $appointments->count(),
            'available' => self::availableSlots($model, $appointments->count()),
            'time' => self::firstAppointmentTime($model, $appointments),
        ];
    }

    private static function availableSlots(Model $model, int $booked): int
    {
        $start = Carbon::parse($model->date . ' ' . $model->start_time);
        $end = Carbon::parse($model->date . ' ' . $model->end_time);

        $totalSlots = intdiv(
            $start->diffInMinutes($end),
            $model->appointment_duration
        );

        return max(0, ($totalSlots + $model->queue_length) - $booked);
    }

    private static function firstAppointmentTime(Model $model, Collection $appointments): string
    {
        $time = $appointments->isNotEmpty()
            ? Carbon::parse($appointments->first()->date)->format('H:i')
            : Carbon::parse($model->start_time)->format('H:i');

        return ArabicDateFormatter::formatTime($time);
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
