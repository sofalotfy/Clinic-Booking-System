<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Services\Appointments\GetUpComingAppointment;
use App\Models\DoctorWhatsAppAccount;

class MainMenu
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::find($conversation->doctor_whatsapp_account_id);

        $appointment = GetUpComingAppointment::execute(
            $conversation->patient_id,
            $account->doctor_id
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
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                "Hi {$appointment->patient->user->name}, Your appointment is at {$appointment->start_time}"
            );

            return;
        }

        SendMessage::execute(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            "No appointment is scheduled"
        );
    }
}