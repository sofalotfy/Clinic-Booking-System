<?php

namespace App\Services\Notifications\Doctor\Appointments;

use App\Models\Notification;
use App\Enums\NotificationsType;

class NewBooking
{
    public static function execute($appointment)
    {
        $doctor = $appointment->doctor;

        Notification::create([
                'user_id'  => $doctor->user_id,
                'type'     => NotificationsType::APPOINTMENTS,
                'title' => 'New appointment',
                'text'  => "New appointment booked by {$appointment->patient->user->name} at {$appointment->date}",
            ]);
    }
}