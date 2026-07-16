<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ConversationRouter;
use App\APIServices\WhatsApp\ExecutionRouter;

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
        if ($message['type'] !== 'interactive') {return;}
        switch ($message['value']) {

            case 'confirm':
                $conversation->patient->user->update([
                    'name' => $conversation->data['name'],
                ]);
                
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'Your profile has been updated.',
                );


                $data = $conversation->data;

                $state = array_shift($data['callStack']);

                $conversation->update([
                    'state' => $state,
                    'data'  => $data,
                ]);

                return ExecutionRouter::execute($conversation, $message);

                break;

            case 'cancel':
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        // Unknown button -> show the menu again
        return self::execute($conversation, [
            'type' => 'text',
            'from' => $message['from'],
        ]);
    }
}