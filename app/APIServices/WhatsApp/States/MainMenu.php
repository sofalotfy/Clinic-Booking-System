<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\GetUpComingAppointment;

class MainMenu
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $appointment = GetUpComingAppointment::execute(
            $conversation->patient_id,
            $account->doctor_id
        );

        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
        ]);

        if ($appointment) {

            $conversation->putData('appointment_id', $appointment->id);

            return self::sendAppointmentMenu(
                $account,
                $conversation,
                $message,
                $appointment
            );
        }

        return self::sendBookingMenu(
            $account,
            $conversation,
            $message
        );

    }

    private static function sendAppointmentMenu($account, $conversation, $message, $appointment) {

        $greeting = $conversation->patient->user->name
            ? "Hi {$conversation->patient->user->name},\n\n"
            : '';


        SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $greeting . "You have an appointment on {$appointment->date} at {$appointment->start_time}.\n\nPlease choose an option:",
            [
                [
                    'id' => 'confirm_appointment',
                    'title' => 'Confirm',
                ],
                [
                    'id' => 'reschedule_appointment',
                    'title' => 'Reschedule',
                ],
                [
                    'id' => 'cancel_appointment',
                    'title' => 'Cancel',
                ],
            ]
        );
    }

    private static function sendBookingMenu($account, $conversation, $message)
    {
        $greeting = $conversation->patient->user->name
            ? "Hi {$conversation->patient->user->name},\n\n"
            : "{$message['value']}";

        SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $greeting . "You don't have any upcoming appointments.\n\nPlease choose an option:",
            [
                [
                    'id' => 'book_appointment',
                    'title' => 'Book Appointment',
                ],
                [
                    'id' => 'end_conversation',
                    'title' => 'End Conversation',
                ],
            ]
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