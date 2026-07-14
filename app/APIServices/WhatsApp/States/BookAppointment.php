<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\GetUpComingAppointment;

class BookAppointment
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        return SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'hello :D',
        );
    }


    public static function handleResponse($conversation, $message)
    {
        switch ($message['value']) {

            case 'confirm_appointment':
                // TODO: ConfirmAppointment::execute(...)
                break;

            case 'reschedule_appointment':
                $conversation->update([
                    'state' => ConversationState::RESCHEDULE_APPOINTMENT,
                ]);

                // return RescheduleAppointment::execute($conversation, $message);
                break;

            case 'cancel_appointment':
                $conversation->update([
                    'state' => ConversationState::CANCEL_APPOINTMENT,
                ]);

                // return CancelAppointment::execute($conversation, $message);
                break;

            case 'book_appointment':
                $conversation->update([
                    'state' => ConversationState::BOOK_APPOINTMENT,
                ]);

                return BookAppointment::execute($conversation, $message);
                break;

            case 'end_conversation':
                $conversation->update([
                    'state' => ConversationState::START,
                    'step' => null,
                    'data' => [],
                ]);

                return;
        }

        // Unknown button -> show the menu again
        return self::execute($conversation, [
            'type' => 'text',
            'from' => $message['from'],
        ]);
    }
}