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

        $oldDateRaw = $conversation->data['old_date'] ?? null;

        $newDate = ArabicDateFormatter::format(
            Carbon::parse($appointment->date . ' ' . $appointment->start_time)
        );

        $oldDate = $oldDateRaw
            ? ArabicDateFormatter::format(Carbon::parse($oldDateRaw))
            : null;

        $messageText = "لظروف خارجة عن إرادتنا نأسف لتعديل موعد زيارتك القادمة يوم {$oldDate} لتصبح يوم {$newDate}";

        return SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $messageText,
            [
                [
                    'id' => 'confirm',
                    'title' => 'تأكيد الموعد',
                ],
                [
                    'id' => 'reschedule',
                    'title' => 'إختيار موعد جديد',
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
            
            $conversation->update([
                'state' => ConversationState::AI,
            ]);

            return ExecutionRouter::execute($conversation, $message);
        }
        
        switch ($message['value']) {

            case 'confirm':
                ConfirmAppointment::execute($conversation->user, $appointment);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم تأكيد موعدك بنجاح'
                );

                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);

                break;

            case 'reschedule':

                $conversation->update([
                    'state' => ConversationState::BOOK_APPOINTMENT,
                ]);

                return BookAppointment::execute($conversation, $message);

                break;

            case 'cancel':
                DenyAppointmentConfirmation::execute($conversation->user, $appointment);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم رفض الموعد'
                );
                
                $conversation->update([
                    'state' => ConversationState::START,
                ]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}