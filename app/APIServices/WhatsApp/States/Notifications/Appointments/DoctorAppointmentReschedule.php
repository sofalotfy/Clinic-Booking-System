<?php

namespace App\APIServices\WhatsApp\States\Notifications\Appointments;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\Appointment;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;
use App\Services\Appointments\Modifications\ConfirmAppointment;
use App\Services\Appointments\Modifications\DenyAppointmentConfirmation;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

class DoctorAppointmentReschedule
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        $appointment = Appointment::findOrFail($conversation->data['appointment_id']);

        // ---- the block you asked about ----
        $includeTime = ! ($conversation->data['is_queued'] ?? false);

        $newDate = ArabicDateFormatter::format(
            Carbon::parse($appointment->date . ' ' . $appointment->start_time),
            includeTime: $includeTime
        );

        $oldDateRaw = $conversation->data['old_date'] ?? null;

        $oldDate = $oldDateRaw
            ? ArabicDateFormatter::format(Carbon::parse($oldDateRaw), includeTime: $includeTime)
            : '-';
        // -----------------------------------

        return SendMessage::template(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $conversation->data['template_name'],
            'ar_EG',
            bodyParams: [
                'old_date' => $oldDate,
                'new_date' => $newDate,
            ],
            quickReplies: [
                0 => 'confirm',
                1 => 'reschedule',
                2 => 'cancel',
            ]
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        // Template quick replies arrive as "button", normal interactive replies as "interactive"
        if (!in_array($message['type'], ['interactive', 'button'], true)) {
            return self::execute($conversation, $message);
        }

        $value = $message['value'] ?? ($message['button']['payload'] ?? null);

        $appointment = Appointment::find($conversation->data['appointment_id']);
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        switch ($value) {
            case 'confirm':
                ConfirmAppointment::execute($conversation->user, $appointment);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم تأكيد موعدك بنجاح'
                );

                $conversation->update(['state' => ConversationState::START]);

                return Start::execute($conversation, $message);

            case 'reschedule':
                $conversation->update(['state' => ConversationState::BOOK_APPOINTMENT]);

                return BookAppointment::execute($conversation, $message);

            case 'cancel':
                DenyAppointmentConfirmation::execute($conversation->user, $appointment);

                SendMessage::text(
                    $account->phone_number_id,
                    $account->access_token,
                    $message['from'],
                    'تم رفض الموعد'
                );

                $conversation->update(['state' => ConversationState::START]);

                return Start::execute($conversation, $message);
        }

        return self::execute($conversation, $message);
    }
}