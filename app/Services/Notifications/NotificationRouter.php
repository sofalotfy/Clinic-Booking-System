<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Notifications\Handlers\Handler;
use App\Enums\NotificationEnum;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Services\Notifications\Handlers\PatientAppointmentBooked;
use App\Services\Notifications\Handlers\PatientAppointmentRescheduled;

class NotificationRouter
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        $handler = match ($notification->type()) {
            NotificationEnum::PATIENT_APPOINTMENT_BOOKED => PatientAppointmentBooked::class,
            NotificationEnum::PATIENT_APPOINTMENT_RESCHEDULED => PatientAppointmentRescheduled::class,
            default => Handler::class,
        };

        $handler::execute($sender, $clinicId, $notification, $receivers, $model);
    }
}