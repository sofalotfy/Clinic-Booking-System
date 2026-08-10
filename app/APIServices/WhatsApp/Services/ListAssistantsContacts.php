<?php

namespace App\APIServices\WhatsApp\Services;

use App\APIServices\WhatsApp\SendMessage;
use App\Models\WhatsAppConversation;

class ListAssistantsContacts
{
    public static function execute(WhatsAppConversation $conversation, array $message, array $list)
    {
        $doctorAccount = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        
        SendMessage::text(
                $doctorAccount->phone_number_id,
                $doctorAccount->access_token,
                $message['from'],
                self::buildMessage($list)
            );
    }

    private static function buildMessage(array $list): string
    {
        $message = "Here are the available contacts:\n\n";

        foreach ($list as $contact) {
            $message .= "*Name*: {$contact['name']}\n";
            $message .= "*Phone*: {$contact['phone']}\n\n";
        }

        return $message;
    }   
}