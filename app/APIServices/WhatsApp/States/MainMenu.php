<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Services\Appointments\GetUpComingAppointment;

class MainMenu
{
    public static function execute($conversation, $message)
    {
        $appointment = GetUpComingAppointment::execute(
            $conversation->patient_id,
            $conversation->doctorWhatsAppAccount->doctor_id
        );

        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
        ]);

        if ($appointment) {

            $conversation->update([
                'data' => [
                    'appointment_id' => $appointment->id,
                ]
            ]);

            SendMessage::execute(
                $conversation->doctorWhatsAppAccount->phone_number_id,
                $conversation->doctorWhatsAppAccount->access_token,
                $message['from'],
                "Hi {$appointment->patient->user->name}, Your appointment is at {$appointment->start_time}"
            );

            return;
        }

        SendMessage::execute(
            $conversation->doctorWhatsAppAccount->phone_number_id,
            $conversation->doctorWhatsAppAccount->access_token,
            $message['from'],
            "No appointment is scheduled"
        );
    }
}