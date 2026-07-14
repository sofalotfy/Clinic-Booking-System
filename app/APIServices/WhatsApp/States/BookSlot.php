<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\Days\GetAvailableSlots;
use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;

class BookSlot
{
    private const PAGE_SIZE = 9;

    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $page = $conversation->data['slot_page'] ?? 0;

        $slots = GetAvailableSlots::execute(
            $conversation->data['selected_day']
        );

        if (empty($slots)) {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'No available slots for this day.'
            );

            return;
        }

        $rows = collect($slots)
            ->slice($page * self::PAGE_SIZE, self::PAGE_SIZE)
            ->map(function ($slot) {
                return [
                    'id' => $slot['time'],
                    'title' => $slot['time'],
                ];
            })
            ->values();

        if (count($slots) > (($page + 1) * self::PAGE_SIZE)) {
            $rows->push([
                'id' => 'more_slots',
                'title' => '➡️ More times',
            ]);
        }

        SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'Please choose a time slot.',
            'Select Slot',
            $rows->toArray(),
            'Available Slots',
            'Day'
        );
    }

    public static function handleResponse($conversation, $message)
    {
        // User requested the next page
        if ($message['value'] === 'more_slots') {

            $conversation->update([
                'data' => array_merge(
                    $conversation->data ?? [],
                    [
                        'slot_page' => ($conversation->data['slot_page'] ?? 0) + 1,
                    ]
                ),
            ]);

            $conversation->refresh();

            return self::execute($conversation, $message);
        }

        // User selected a day (coming from BookAppointment)
        if ($conversation->state !== ConversationState::BOOK_SLOT) {

            $conversation->update([
                'state' => ConversationState::BOOK_SLOT,
                'data' => array_merge(
                    $conversation->data ?? [],
                    [
                        'selected_day' => $message['value'],
                        'slot_page' => 0,
                    ]
                ),
            ]);

            $conversation->refresh();

            return self::execute($conversation, $message);
        }

        // User selected a slot
        $conversation->update([
            'state' => ConversationState::CONFIRM_BOOKING,
            'data' => array_merge(
                $conversation->data ?? [],
                [
                    'selected_slot' => $message['value'],
                ]
            ),
        ]);

        // TODO:
        // return ConfirmBooking::execute($conversation, $message);
    }
}