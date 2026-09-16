<?php

namespace App\APIServices\WhatsApp\States\Notifications\Appointments;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;
use App\Services\Appointments\Modifications\ConfirmAppointment;
use App\Services\Appointments\Modifications\DenyAppointmentConfirmation;
use App\Models\Appointment;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class DoctorAppointmentBooking
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $appointment = Appointment::find($conversation->data['appointment_id']);
        $userName = $conversation->user->name;

        $dateTime = Carbon::parse($appointment->date . ' ' . $appointment->start_time);
        $formattedDate = ArabicDateFormatter::format($dateTime);

        $text = "شكرا {$userName}\nتم حجز موعدك يوم {$formattedDate}";

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
                    'title' => 'إلغاء الموعد',
                ],
            ]
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        $appointment = Appointment::find($conversation->data['appointment_id']);
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);
        }

        $userName = $conversation->user->name;
        $dateTime = Carbon::parse($appointment->date . ' ' . $appointment->start_time);
        $formattedDate = ArabicDateFormatter::format($dateTime);

        switch ($message['value']) {

            case 'confirm':
                ConfirmAppointment::execute($conversation->user, $appointment);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    "شكرا {$userName}\nتم تأكيد حجز موعدك يوم {$formattedDate} بنجاح",
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

            case 'cancel':
                DenyAppointmentConfirmation::execute($conversation->user, $appointment);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    "أهلا {$userName}\nنأسف لإلغاء موعدك يوم {$formattedDate} لظروف خاصة",
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}