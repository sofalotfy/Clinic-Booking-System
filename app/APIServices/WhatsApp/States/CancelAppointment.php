<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Models\Appointment;

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
            "Are u sure you want to cancel your appointment?:",
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
        switch ($message['value']) {

            case 'confirm':
                $appointment = Appointment::find($conversation->data['appointment_id']);
                $appointment->delete();

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