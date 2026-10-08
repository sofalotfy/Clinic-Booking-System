<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\AppointmentStatus;
use App\Enums\ConversationState;
use App\Models\Day;
use App\Models\DoctorWhatsAppAccount;
use App\Services\Appointments\Creation\SmartBookAppointment;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

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

        $dateTime = Carbon::parse($day->date.' '.$time);
        $formattedDate = ArabicDateFormatter::format($dateTime);

        $userName = $conversation->data['name'];

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
            return self::invalidResponse($conversation, $message);
        }

        switch ($message['value']) {

            case 'confirm':
                $day = Day::find($conversation->data['selected_day']);
                $isAvailable = CheckAvailability::execute($day);
                $time = $isAvailable
                    ? $conversation->data['selected_slot']
                    : '00:00';
                $dateTime = Carbon::parse($day->date.' '.$time);

                try {
                    SmartBookAppointment::execute(
                        $conversation->patient(),
                        $account->doctor,
                        $dateTime,
                        $day->appointment_duration,
                        $conversation->data['booking_state'],
                    );
                } catch (ValidationException $exception) {
                    return self::handleBookingError($conversation, $message, $exception);
                }

                $userName = $conversation->data['name'];

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

        return self::invalidResponse($conversation, $message);
    }

    private static function handleBookingError($conversation, $message, ValidationException $exception)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            self::arabicBookingError($exception)."\nمن فضلك اختر يوما اخر",
        );

        $conversation->update(['state' => ConversationState::BOOK_APPOINTMENT]);

        return BookAppointment::execute($conversation, $message);
    }

    private static function arabicBookingError(ValidationException $exception): string
    {
        $englishToArabic = [
            'The day is not available.' => 'عذرا، هذا اليوم غير متاح',
            'This time has already passed.' => 'عذرا، هذا الوقت قد مر بالفعل',
            'You have reached the maximum number of appointments.' => 'عذرا، لقد وصلت إلى الحد الأقصى من المواعيد',
            'This slot is not available.' => 'عذرا، هذا الوقت غير متاح',
            'This slot is already booked.' => 'عذرا، هذا الوقت محجوز بالفعل',
            'The Day is fully booked.' => 'عذرا، هذا اليوم ممتلئ بالكامل',
        ];

        $english = $exception->errors()['error'][0] ?? null;

        return $englishToArabic[$english] ?? 'عذرا، لم نتمكن من تأكيد الحجز';
    }

    private static function invalidResponse($conversation, $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        SendMessage::text(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            'نأسف لعدم تفهمنا لرسالتك 
فضلا اختر أحد الخيارات المتاحة',
        );

        return self::execute($conversation, $message);
    }
}
