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

            self::sendAppointmentMenu(
                $account,
                $conversation,
                $message,
                $appointment
            );
        }

        self::sendBookingMenu(
            $account,
            $conversation,
            $message
        );

        return;
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
            : '';

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
}