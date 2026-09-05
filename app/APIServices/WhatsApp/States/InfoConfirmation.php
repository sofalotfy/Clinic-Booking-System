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
            "تاكيد المعلومات: 
            الاسم: {$conversation->data['name']}
            العمر: {$conversation->data['age']}
            العنوان: {$conversation->data['address']}",
            [
                [
                    'id' => 'confirm',
                    'title' => 'تاكيد',
                ],
                [
                    'id' => 'cancel',
                    'title' => 'الغاء',
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
            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }
        switch ($message['value']) {

            case 'confirm':
                $oldName = $conversation->user->name;
                $newName = $conversation->data['name'];
                
                $conversation->user->update([
                    'name' => $newName,
                    'age' => $conversation->data['age'],
                    'area' => $conversation->data['address'],
                ]);

                if ($oldName !== $newName) {
                    $patient = $conversation->patient();
                    PatientRename::execute($patient, $oldName, $newName);
                }
                
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم تحديث معلوماتك بنجاح.',
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