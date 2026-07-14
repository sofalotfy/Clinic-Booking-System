<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;

class InfoConfirmation
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            "Confirm your name: {$conversation->data['name']}",
            [
                [
                    'id' => 'confirm',
                    'title' => 'Confirm',
                ],
                [
                    'id' => 'cancel',
                    'title' => 'Cancel',
                ],
            ]
        );

    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        $user = $conversation->patient->user;

        $user->update([
            'name' => $message['value'],
        ]);

        $conversation->update([
            'state' => ConversationState::INFO_CONFIRMATION,
        ]);

        return InfoConfirmation::execute($conversation, $message);
    }
}