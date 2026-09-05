<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Models\Patient;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatelessNotification;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use App\Services\Notifications\Handlers\Handler;

class PatientAppointmentRescheduled extends Handler
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        $title = static::buildTitle($model, $notification);
        $body = static::buildBody($model, $notification);

        static::dispatch($sender, $clinicId, $notification, $receivers, $title, $body);
    }

    private static function buildTitle(Model $model, $notification): string
    {
        return $notification->title();
    }

    private static function buildBody(Model $model, $notification): string
    {
        $dateTime = Carbon::parse("{$model->date} {$model->start_time}")->format('M j, Y g:i A');

        return $notification->body([
            'patient_name' => $model->patient->user->name,
            'date' => $dateTime,
        ]);
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, string $title, string $body)
    {
        SendWhatsAppStatelessNotification::execute($sender, $receiver, $clinicId, $title, $body);
    }
}