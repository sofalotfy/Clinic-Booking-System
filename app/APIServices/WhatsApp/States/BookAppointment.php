<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\APIServices\WhatsApp\States\BookSlot;
use App\Enums\ConversationState;
use App\Models\Day;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Doctors\Retrievals\GetAvailableDays;
use App\Services\TemplatePlans\Checks\CheckAvailability;

class BookAppointment
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if(!$conversation->user->name){
            $conversation->update([
                'state' => ConversationState::INFO_INQUIRY,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['callStack' => array_merge(
                            [ConversationState::BOOK_APPOINTMENT],
                            $conversation->data['callStack'] ?? []
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
            'من فضلك اختر يوم',
            'اختر يوم',
            collect($days)->map(function ($day) {
                return [
                    'id' => $day['id'],
                    'title' => "{$day['day']} - {$day['date']}",
                    'description' => $day['note'],
                ];
            })->toArray(),
            'ايام متاحه',
            'اخر 7 ايام'
        );
    }


    public static function handleResponse($conversation, $message)
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

        $day = Day::where('doctor_id', $account->doctor_id)->find($message['value']);

        if (!$day) {return;}

        if(CheckAvailability::execute($day)){
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