<?php

namespace App\Services\Notifications\Doctor\Appointments;

use App\Models\Notification;
use App\Enums\NotificationsType;


class BookingCancled
{
    public static function execute($appointment)
    {
        $doctor = $appointment->doctor;

        Notification::create([
                'user_id'  => $doctor->user_id,
                'type'     => NotificationsType::APPOINTMENTS,
                'title' => 'Appointment Cancled',
                'text'  => "patient {$appointment->patient->user->name} cancled his appointment from {$appointment->date}",
            ]);
    }
}