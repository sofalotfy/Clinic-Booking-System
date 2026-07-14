<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;

class InfoInquiry
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        return SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'Please enter your name.',
        );

    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        $conversation->update([
            'data' => $conversation->data + ['name' => $message['value']],
            'state' => ConversationState::INFO_CONFIRMATION,
        ]);

        return InfoConfirmation::execute($conversation, $message);
    }
}