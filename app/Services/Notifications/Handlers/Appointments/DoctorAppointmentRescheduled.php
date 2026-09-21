<?php

namespace App\Services\Notifications\Handlers\Appointments;

use App\Models\User;
use App\Services\Notifications\Channels\SendWhatsAppStatefulNotification;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use App\Services\Notifications\Handlers\Handler;
use App\Enums\AppointmentStatus;

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
        if($model->status == AppointmentStatus::QUEUED){
            $from_date = Carbon::parse("{$model->old_date}")->format('M j, Y');
            $to_date   = Carbon::parse("{$model->date}")->format('M j, Y');
        }else{
            $from_date = Carbon::parse("{$model->old_date}")->format('M j, Y g:i A');
            $to_date   = Carbon::parse("{$model->date}")->format('M j, Y g:i A');
        }

        return $notification->body([
            'from_date' => $from_date,
            'to_date'   => $to_date,
        ]);
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body)
    {
        $oldDateTime = Carbon::parse("{$model->old_date}")->format('M j, Y g:i A');

        SendWhatsAppStatefulNotification::execute($sender, $receiver, $clinicId, $notification,[
            'appointment_id' => $model->id,
            'old_date' => $oldDateTime,
        ]);
    }
}