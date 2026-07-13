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
        SendMessage::execute(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            "You have an appointment on {$appointment->date} at {$appointment->start_time}.

            Please choose an option:

            1️⃣ Confirm Appointment
            2️⃣ Reschedule Appointment
            3️⃣ Cancel Appointment"
        );
    }

    private static function sendBookingMenu($account, $conversation, $message)
    {
        $name = $conversation->patient?->user?->name ?? 'there';

        SendMessage::execute(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            "You don't have any upcoming appointments.

            Please choose an option:

            1️⃣ Book Appointment
            2️⃣ End Conversation"
        );
    }
}