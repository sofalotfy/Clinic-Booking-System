<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\Days\GetAvailableSlots;
use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ExecutionRouter;

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
                'عذرا، لا يوجد مواعيد متاحه لهذا اليوم، من فضلك اختر يوم اخر'
            );

            $conversation->update([
                'state' => ConversationState::BOOK_APPOINTMENT,
            ]);

            return BookAppointment::execute($conversation, $message);
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

        // Page became invalid because availability changed
        if ($rows->isEmpty()) {

            $conversation->update([
                'data' => array_merge(
                    $conversation->data ?? [],
                    [
                        'slot_page' => 0,
                    ]
                ),
            ]);

            return self::execute($conversation, $message);
        }

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
            'من فضلك اختر الوقت المفضل لديك',
            'اختر الوقت',
            $rows->toArray(),
            'الوقت المتاح',
            'الوقت'
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        // This state only accepts interactive list replies
        if ($message['type'] !== 'interactive') {

            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }

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

            return self::execute($conversation, $message);
        }

        // Validate that the selected slot is still available
        $slots = GetAvailableSlots::execute(
            $conversation->data['selected_day']
        );

        $validSlots = collect($slots)
            ->pluck('time')
            ->all();

        if (! in_array($message['value'], $validSlots, true)) {

            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $message['from'],
                'الوقت غير متاح الان. من فضلك اختر وقت اخر.'
            );

            return self::execute($conversation, $message);
        }

        // Save the selected slot
        $conversation->update([
            'state' => ConversationState::CONFIRM_BOOKING,
            'data' => array_merge(
                $conversation->data ?? [],
                [
                    'selected_slot' => $message['value'],
                ]
            ),
        ]);

        return ConfirmBooking::execute($conversation, $message);
    }
}