<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\GetUpComingAppointment;
use App\APIServices\Doctors\GetAvailableDays;
use App\APIServices\WhatsApp\States\BookSlot;
use App\Models\Day;
use App\APIServices\Days\CheckAvailability;


class BookAppointment
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if(!$conversation->patient->user->name){
            $conversation->update([
                'state' => ConversationState::INFO_INQUIRY,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['callStack' => array_merge(
                            [ConversationState::BOOK_APPOINTMENT],
                            $conversation->data->callStack ?? [],
                        )
                    ]
                ),
            ]);

            return InfoInquiry::execute($conversation, $message);
        }

        $days = GetAvailableDays::execute($account->doctor_id);

        SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'Please choose an appointment day.',
            'Select Day',
            collect($days)->map(function ($day) {
                return [
                    'id' => $day['id'],
                    'title' => "{$day['day']} - {$day['date']}",
                    'description' => $day['note'],
                ];
            })->toArray(),
            'Available Days',
            'Next 7 Days'
        );
        \Log::info('here');
    }


    public static function handleResponse($conversation, $message)
    {
        $day = Day::where('id' , $message['value']);

        if(CheckAvailability::execute($day->id)){
            $conversation->update([
                'state' => ConversationState::BOOK_SLOT,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['selected_day' => $day->id],
                ),
            ]);

            return BookSlot::execute($conversation, $message); 
        }else{
            $conversation->update([
                'state' => ConversationState::CONFIRM_BOOKING,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['selected_day' => $day->id],
                ),
            ]);

            return ConfirmBooking::execute($conversation, $message); 
        }        
    }
}