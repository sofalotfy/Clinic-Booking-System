<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;

class IdleState
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $conversation->update([
            'state' => ConversationState::IDLE,
        ]);

        $name = $conversation->user?->name ?? '';
        $greeting = $name ? "Hello {$name}" : "Hello";

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            "{$greeting}, you will be notified with any new notification."
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        return self::execute($conversation, $message);
    }
}
