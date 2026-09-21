<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use App\Services\Notifications\Handlers\Handler;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

class PatientAppointmentRescheduled extends Handler
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
        if ($model->status == AppointmentStatus::QUEUED) {
            $fromDate = Carbon::parse("{$model->old_date}")->format('M j, Y');
            $toDate = Carbon::parse("{$model->date}")->format('M j, Y');
        } else {
            $fromDate = Carbon::parse("{$model->old_date}")->format('M j, Y g:i A');
            $toDate = Carbon::parse("{$model->date}")->format('M j, Y g:i A');
        }

        return $notification->body([
            'patient_name' => $model->patient->user->name,
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    private static function buildWhatsAppParams(Model $model): array
    {
        $patientUser = $model->patient->user;

        // Queued appointments have a date only, no specific time
        $isQueued = $model->status == AppointmentStatus::QUEUED;

        $format = fn ($raw) => $isQueued
            ? ArabicDateFormatter::format(Carbon::parse($raw)->startOfDay())
            : ArabicDateFormatter::format(Carbon::parse($raw));

        return [
            'patient_name' => $patientUser->name,
            'whatsapp' => 'https://wa.me/' . preg_replace('/\D/', '', $patientUser->phone),
            'from_date' => $format($model->old_date),
            'to_date' => $format($model->date),
        ];
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body)
    {
        SendWhatsAppStatelessNotification::execute(
            $sender,
            $receiver,
            $clinicId,
            $notification->templateName(),   // template comes from the notification
            self::buildWhatsAppParams($model)
        );
    }
}