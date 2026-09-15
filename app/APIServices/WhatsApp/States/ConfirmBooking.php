<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\ExecutionRouter;
use App\Enums\ConversationState;
use App\Enums\AppointmentStatus;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Day;
use App\Services\Appointments\Creation\SmartBookAppointment;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class ConfirmBooking
{
    public static function execute($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $day = Day::find($conversation->data['selected_day']);
        $isAvailable = CheckAvailability::execute($day);

        $time = $isAvailable
            ? $conversation->data['selected_slot']
            : '00:00';

        $dateTime = Carbon::parse($day->date . ' ' . $time);
        $formattedDate = ArabicDateFormatter::format($dateTime);

        $userName = $conversation->user()->name;

        if ($isAvailable) {
            $state = AppointmentStatus::ACTIVE;
            $text = "شكرا {$userName}\nتم حجز موعدك يوم {$formattedDate}";
            $buttons = [
                ['id' => 'confirm',    'title' => 'تأكيد الموعد'],
                ['id' => 'reschedule', 'title' => 'اختيار موعد جديد'],
                ['id' => 'cancel',     'title' => 'إلغاء الموعد'],
            ];
        } else {
            $state = AppointmentStatus::QUEUED;
            $text = "شكرا {$userName}\nتم حجز موعدك يوم {$formattedDate}\nعلى قائمة الانتظار";
            $buttons = [
                ['id' => 'confirm', 'title' => 'تأكيد الموعد'],
                ['id' => 'cancel',  'title' => 'الغاء الحجز'],
            ];
        }

        $conversation->update([
            'data' => array_merge(
                $conversation->data ?? [],
                ['booking_state' => $state]
            ),
        ]);

        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $text,
            $buttons
        );
    }

    public static function handleResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);
        }

        switch ($message['value']) {

            case 'confirm':
                $day = Day::find($conversation->data['selected_day']);
                $isAvailable = CheckAvailability::execute($day);
                $time = $isAvailable
                    ? $conversation->data['selected_slot']
                    : '00:00';
                $dateTime = Carbon::parse($day->date . ' ' . $time);

                SmartBookAppointment::execute(
                    $conversation->user(),
                    $account->doctor,
                    $dateTime,
                    $day->appointment_duration,
                    $conversation->data['booking_state'],
                );

                $userName = $conversation->user()->name;

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    "شكرا {$userName}\nتم تأكيد موعدك بنجاح",
                );

                $conversation->update(['state' => ConversationState::START]);

                return Start::execute($conversation, $message);

            case 'reschedule':
                // Only shown on the available-slot (3-button) screen.
                $conversation->update(['state' => ConversationState::BOOK_APPOINTMENT]);

                return BookAppointment::execute($conversation, $message);

            case 'cancel':
                $conversation->update(['state' => ConversationState::START]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}