<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\FeedBacks\CreateFeedBack;

class SubmitFeedback
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $conversation->update([
            'state' => ConversationState::SUBMIT_FEEDBACK,
        ]);

        return SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'من فضلك اكتب رأيك أو شكواك في رسالة نصية',
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'text') {
            return self::execute($conversation, $message);
        }

        $clinic = $account->doctor->clinic;

        CreateFeedBack::execute(
            $conversation->user,
            $clinic,
            $message['value'],
        );

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'شكرا لك، تم إرسال رأيك بنجاح',
        );

        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
        ]);
    }
}