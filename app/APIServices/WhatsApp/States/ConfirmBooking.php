<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\Creation\SmartBookAppointment;
use App\Models\Day;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Enums\AppointmentStatus;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class ConfirmBooking
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $day = Day::findOrFail(
            $conversation->data['selected_day']
        );

        $selectedSlot = $conversation->data['selected_slot'];

        $formattedDate = ArabicDateFormatter::format(
            Carbon::parse($day->date),
            includeTime: false
        );

        $formattedTime = ArabicDateFormatter::formatTime(
            $selectedSlot
        );

        if (CheckAvailability::execute($day)) {
            $state = AppointmentStatus::ACTIVE;

            $text = "فضلا قم بتأكيد حجز موعدك يوم {$formattedDate} الساعة {$formattedTime}";
        } else {
            $state = AppointmentStatus::QUEUED;

            $text = "برجاء تأكيد موعد حجزك يوم {$formattedDate} الساعة {$formattedTime} على قائمة الانتظار";
        }

        $conversation->update([
            'data' => array_merge(
                $conversation->data ?? [],
                [
                    'booking_state' => $state,
                ]
            ),
        ]);

        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $text,
            [
                [
                    'id' => 'confirm',
                    'title' => 'تأكيد الموعد',
                ],
                [
                    'id' => 'cancel',
                    'title' => 'إختيار موعد آخر',
                ],
            ]
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);

            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }

        switch ($message['value']) {

            case 'confirm':

                $day = Day::findOrFail(
                    $conversation->data['selected_day']
                );

                $date = $day->date;

                $time = CheckAvailability::execute($day)
                    ? $conversation->data['selected_slot']
                    : '00:00';

                $dateTime = Carbon::parse($date . ' ' . $time);

                SmartBookAppointment::execute(
                    $conversation->patient(),
                    $account->doctor,
                    $dateTime,
                    $day->appointment_duration,
                    $conversation->data['booking_state'],
                );

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم تأكيد موعدك بنجاح',
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

            case 'cancel':

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}