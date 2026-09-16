<?php

namespace App\APIServices\WhatsApp\States\Notifications\Appointments;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\APIServices\WhatsApp\SendMessage;
use App\Enums\AppointmentUpdateNotificationTypes;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;
use App\Services\Appointments\Modifications\ConfirmAppointment;
use App\Services\Appointments\Modifications\DenyAppointmentConfirmation;
use App\Models\Appointment;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class DoctorAppointmentReschedule
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $appointment = Appointment::find($conversation->data['appointment_id']);

        $userName = $conversation->user->name;

        $newDateTime = Carbon::parse($appointment->date . ' ' . $appointment->start_time);
        $newDate = ArabicDateFormatter::format($newDateTime);

        $oldDateRaw = $conversation->data['old_date'] ?? null;

        if ($oldDateRaw) {
            $oldDate = ArabicDateFormatter::format(Carbon::parse($oldDateRaw));

            $text = "اهلا {$userName}\n"
                . "لظروف خارجة عن إرادتنا نأسف لتعديل موعد زيارتك القادمة يوم {$oldDate} لتصبح يوم {$newDate}";

            $buttons = [
                ['id' => 'confirm',    'title' => 'تأكيد الموعد'],
                ['id' => 'reschedule', 'title' => 'إختيار موعد جديد'],
                ['id' => 'cancel',     'title' => 'إلغاء الموعد'],
            ];
        } else {
            $text = "تم تعديل موعد الحجز بنجاح ليصبح يوم {$newDate}";

            $buttons = [
                ['id' => 'confirm',    'title' => 'تأكيد الحجز'],
                ['id' => 'reschedule', 'title' => 'اختيار موعد آخر'],
            ];
        }

        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $text,
            $buttons
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
                    "شكرا {$userName}\nتم تأكيد موعدك بنجاح",
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

            case 'reschedule':
                // TODO: route to whatever flow lets the patient pick a new slot.
                return self::execute($conversation, $message);

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