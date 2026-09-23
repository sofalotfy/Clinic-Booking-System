<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;
use App\Models\Appointment;
use App\Services\Appointments\Modifications\CancelAppointment as CancelService;

class CancelAppointment
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
            "هل انت متاكد من الغاء الموعد؟:",
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
            return self::invalidResponse($conversation, $message);
        }
        switch ($message['value']) {

            case 'confirm':
                
                $appointment = Appointment::find($conversation->data['appointment_id']);
                CancelService::execute($conversation->user, $appointment, AppointmentUpdateNotificationTypes::CANCEL);

                
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

                break;

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
            'هذا الرد غير صالح، فضلا اختر أحد الخيارات المتاحة',
        );

        return self::execute($conversation, $message);
    }
}