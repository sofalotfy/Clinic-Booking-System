<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Enums\AppointmentStatus;
use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatefulNotification;
use App\Services\Notifications\Handlers\Handler;
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
            'to_date'   => $toDate,
        ]);
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body)
    {
        $data = [
            'appointment_id' => $model->id,
            'is_queued'      => $model->status == AppointmentStatus::QUEUED,
        ];

        // Raw value; the state class formats it in Arabic when sending the template
        if ($model->old_date) {
            $data['old_date'] = Carbon::parse($model->old_date)->toDateTimeString();
        }

        SendWhatsAppStatefulNotification::execute($sender, $receiver, $clinicId, $notification, $data);
    }
}