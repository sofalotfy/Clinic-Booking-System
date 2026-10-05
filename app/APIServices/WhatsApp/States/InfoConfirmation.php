<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ExecutionRouter;

class InfoConfirmation
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $text = "فضلا قم بمراجعة بياناتك المدخلة\n"
            . "الاسم: {$conversation->data['name']}\n"
            . "السن: {$conversation->data['age']}\n"
            . "العنوان: {$conversation->data['address']}";

        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $text,
            [
                [
                    'id' => 'confirm',
                    'title' => 'تأكيد البيانات',
                ],
                [
                    'id' => 'edit',
                    'title' => 'تعديل',
                ],
                [
                    'id' => 'cancel',
                    'title' => 'إلغاء',
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
            return self::invalidResponse($conversation, $message);
        }

        switch ($message['value']) {

            case 'confirm':
                $newName = $conversation->data['name'];

                $conversation->user->update([
                    'name' => $newName,
                    'age' => $conversation->data['age'],
                    'area' => $conversation->data['address'],
                ]);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    "شكرا {$newName}\nتم تحديث بياناتك بنجاح",
                );

                $data = $conversation->data;
                $state = array_shift($data['callStack']);

                $conversation->update([
                    'state' => $state,
                    'data'  => $data,
                ]);

                return ExecutionRouter::execute($conversation, $message);

            case 'edit':
                $conversation->update([
                    'state' => ConversationState::INFO_INQUIRY,
                ]);

                return InfoInquiry::execute($conversation, $message);

            case 'cancel':
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::invalidResponse($conversation, $message);
    }

    private static function invalidResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'نأسف لعدم تفهمنا لرسالتك 
فضلا اختر أحد الخيارات المتاحة',
        );

        return self::execute($conversation, $message);
    }
}