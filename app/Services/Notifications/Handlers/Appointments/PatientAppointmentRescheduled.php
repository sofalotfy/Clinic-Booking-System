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

        static::dispatch($sender, $clinicId, $notification, $receivers, $model, $title, $body);
    }

    private static function buildTitle(Model $model, $notification): string
    {
        return $notification->title();
    }

    private static function buildBody(Model $model, $notification): string
    {
        if($model->status == AppointmentStatus::QUEUED){
            $fromDate = Carbon::parse("{$model->old_date}")->format('M j, Y');
            $toDate = Carbon::parse("{$model->date}")->format('M j, Y');
        }else{
            $fromDate = Carbon::parse("{$model->old_date}")->format('M j, Y g:i A');
            $toDate = Carbon::parse("{$model->date}")->format('M j, Y g:i A');
        }


        return $notification->body([
            'patient_name' => $model->patient->user->name,
            'whatsapp' => 'https://wa.me/' . preg_replace('/\D/', '', $model->patient->user->phone),
            'from_date' => $fromDate,
            'to_date' => $toDate,
        ]);
    }

    protected static function sendWhatsApp(User $sender, User $receiver, int $clinicId, $notification, $model, string $title, string $body)
    {
        SendWhatsAppStatelessNotification::execute($sender, $receiver, $clinicId, $title, $body);
    }
}