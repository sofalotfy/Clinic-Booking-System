<?php

namespace App\Services\Notifications\Doctor\Appointments;

use App\Models\Notification;
use App\Enums\NotificationsType;


class BookingReshedule
{
    public static function execute($appointment, $oldDate, $newDate)
    {
        $doctor = $appointment->doctor;

        Notification::create([
                'user_id'  => $doctor->user_id,
                'type'     => NotificationsType::APPOINTMENTS,
                'title' => 'Appointment Reshedule',
                'text'  => "patient {$appointment->patient->user->name} resheduled appointment from {$oldDate} to {$newDate}",
            ]);
    }
}