<?php

namespace App\Services\Notifications;

use App\Models\User;
use App\Services\Notifications\Handlers\Handler;
use App\Enums\NotificationEnum;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use App\Services\Notifications\Handlers\Appointments\PatientAppointmentBooked;
use App\Services\Notifications\Handlers\Appointments\PatientAppointmentRescheduled;
use App\Services\Notifications\Handlers\Appointments\PatientAppointmentCancel;
use App\Services\Notifications\Handlers\Appointments\DoctorAppointmentBooked;
use App\Services\Notifications\Handlers\Appointments\DoctorAppointmentRescheduled;
use App\Services\Notifications\Handlers\Appointments\DoctorAppointmentCancel;

class NotificationRouter
{
    public static function execute(User $sender, int $clinicId, $notification, Collection $receivers, Model $model)
    {
        $handler = match ($notification->type()) {
            NotificationEnum::PATIENT_APPOINTMENT_BOOKED => PatientAppointmentBooked::class,
            NotificationEnum::PATIENT_APPOINTMENT_RESCHEDULED => PatientAppointmentRescheduled::class,
            NotificationEnum::PATIENT_APPOINTMENT_CANCEL => PatientAppointmentCancel::class,
            NotificationEnum::DOCTOR_APPOINTMENT_BOOKED => DoctorAppointmentBooked::class,
            NotificationEnum::DOCTOR_APPOINTMENT_RESCHEDULED => DoctorAppointmentRescheduled::class,
            NotificationEnum::DOCTOR_APPOINTMENT_CANCEL => DoctorAppointmentCancel::class,
            default => Handler::class,
        };

        $handler::execute($sender, $clinicId, $notification, $receivers, $model);
    }
}