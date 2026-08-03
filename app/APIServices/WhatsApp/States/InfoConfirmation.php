<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ConversationRouter;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Services\Notifications\Doctor\Profile\PatientRename;

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
            "Confirm your information: 
            Name: {$conversation->data['name']}
            Age: {$conversation->data['age']}
            Address: {$conversation->data['address']}",
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

        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'Please confirm or cancel your name.',
            );
            
            return self::execute($conversation, $message);
        }
        switch ($message['value']) {

            case 'confirm':
                $oldName = $conversation->patient->user->name;
                $newName = $conversation->data['name'];
                
                $conversation->patient->user->update([
                    'name' => $newName,
                    'age' => $conversation->data['age'],
                    'area' => $conversation->data['address'],
                ]);

                if ($oldName !== $newName) {
                    $patient = $conversation->patient;
                    PatientRename::execute($patient, $oldName, $newName);
                }
                
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