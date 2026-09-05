<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Models\Appointment;
use App\Services\Appointments\CancelAppointment as CancelService;
use App\Enums\AppointmentUpdateNotificationTypes;

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
            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }
        switch ($message['value']) {

            case 'confirm':
                
                $appointment = Appointment::find($conversation->data['appointment_id']);
                CancelService::execute($conversation->patient(), $appointment, AppointmentUpdateNotificationTypes::CANCEL);

                
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

        // Unknown button -> show the menu again
        return self::execute($conversation, [
            'type' => 'text',
            'from' => $message['from'],
        ]);
    }
}