<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\GetUpComingAppointment;
use App\APIServices\Doctors\GetAvailableDays;
USE App\Models\Day;
use App\APIServices\Days\GetAvailableSlots;


class BookSlot
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        \Log::info(json_encode($conversation->data['selected_day']));

        $slots = GetAvailableSlots::execute($conversation->data['selected_day']);

        $rows = collect($slots)
            ->slice($page * 9, 9)
            ->map(fn ($slot) => [
                'id' => $slot['time'],
                'title' => $slot['time'],
            ])
            ->values();

        if (count($slots) > (($page + 1) * 9)) {
            $rows->push([
                'id' => 'more_slots',
                'title' => '➡️ More times',
            ]);
        }

        \Log::info(json_encode($slots));
        

        if (empty($slots)) {
            // SendMessage::execute(
            //     $conversation->phone_number,
            //     "No available slots for this day.",
            //     ['from' => $conversation->whatsapp_number]
            // );
            // return;
        }

        SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'Please choose a Time Slot.',
            'Select Slot',
            $rows,
            'Available Slots',
            'Day'
        );
        
    }


    public static function handleResponse($conversation, $message)
    {
        $day = Day::findOrFail($message['value']);

        $conversation->update([
                'state' => ConversationState::BOOK_SLOT,
                'data' => array_merge(
                    $conversation->data ?? [],
                    ['selected_day' => $day->id],
                ),
            ]);

            return BookSlot::execute($conversation, $message);
        return;   
    }
}