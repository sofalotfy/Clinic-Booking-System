<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Enums\AppointmentStatus;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Day;
use App\Models\Appointment;
use App\APIServices\Days\CheckAvailability;
use App\APIServices\Appointments\SmartBookAppointment;
use Carbon\Carbon;


class ConfirmReshedule
{

    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        
        

        $rescheduleType = $conversation->data['reschedule_type'] ?? null;
        $appointment = Appointment::find($conversation->data['appointment_id']);
        $newDate = $conversation->data['new_date'] ?? $appointment->date;

        if ($rescheduleType === 'truncate') {
            $text = "We are sorry, but our clinic schedule has changed, and we no longer work on this day. Your appointment has been Cancelled. Would you like to book another appointment?";
        } elseif ($rescheduleType === 'overflow') {
            $text = "Due to a schedule update, all slots for this day are full. Your appointment has been moved to the waiting list. Would you like to confirm?";
        } else {
            // collide / default
            $text = "Your appointment has been rescheduled to {$newDate}. Would you like to confirm?";
        }
            
        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $appointment->patient->user->phone,
            $text,
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

    public static function handleResponse($conversation, $message)
    {
        $appointment = Appointment::find($conversation->data['appointment_id']);
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'Please choose a valid option.',
            );
            
            return self::execute($conversation, $message);
        }
        
        switch ($message['value']) {

            case 'confirm':
                $appointment->update([
                    'isConfirmed'  =>  true,
                ]);
                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'Your appointment has been confirmed.'
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

                break;

            case 'cancel':
                $appointment->delete();
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}