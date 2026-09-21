<?php

namespace App\APIServices\WhatsApp\States\Notifications\Appointments;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\Appointment;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;
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

        $dateTo = ArabicDateFormatter::format(
            Carbon::parse($appointment->date . ' ' . $appointment->start_time)
        );

        $oldDateRaw = $conversation->data['old_date'] ?? null;
        \Log::info("old date raw: " . $oldDateRaw);
        return true;
        // Template variables can't be empty, so fall back to a dash
        $dateFrom = $oldDateRaw
            ? ArabicDateFormatter::format(Carbon::parse($oldDateRaw))
            : '-';

        return SendMessage::template(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $conversation->data['template_name'],
            'ar_EG',
            bodyParams: [
                'name' => $conversation->data['name'] ?? $conversation->user->name,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
            ]
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        // The template has no buttons, so any reply just returns the user to the main flow
        $conversation->update([
            'state' => ConversationState::START,
        ]);

        return Start::execute($conversation, $message);
    }
}