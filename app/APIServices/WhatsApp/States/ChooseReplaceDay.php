<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\Services\DoctorMenu\ReplaceTodayAppointments;
use App\Services\DaysInstances\Retrievals\GetEmptyDays;
use App\Support\ArabicDateFormatter;
use App\Models\DoctorWhatsAppAccount;
use Carbon\Carbon;

class ChooseReplaceDay
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );
        $doctor = $account->doctor;
        $user = $doctor->user;

        $conversation->update([
            'state' => ConversationState::CHOOSE_REPLACE_DAY,
        ]);

        $emptyDates = GetEmptyDays::execute($user, $doctor->id);
        $rows = collect($emptyDates)->map(function ($date) {
            return [
                'id' => $date,
                'title' => ArabicDateFormatter::format(Carbon::parse($date), false),
                'description' => '',
            ];
        })->toArray();

        SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            'اختر يوماً لتبديل المواعيد',
            'اختر يوماً',
            $rows,
            'اختر اليوم',
            'الخيارات'
        );
    }

    public static function handleResponse($conversation, $message)
    {
        if ($message['type'] !== 'interactive') {
            return self::execute($conversation, $message);
        }

        $selectedDate = $message['value'] ?? null;
        if ($selectedDate) {
            ReplaceTodayAppointments::execute($conversation, $selectedDate);
            
            $conversation->update([
                'state' => ConversationState::ADMIN_MENU,
            ]);
            return;
        }
    }
}
