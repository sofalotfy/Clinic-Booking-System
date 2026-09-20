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
    private const PAGE_SIZE = 9;

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

        $page = $conversation->data['replace_day_page'] ?? 0;

        $emptyDates = collect(GetEmptyDays::execute($user, $doctor->id));

        $rows = $emptyDates
            ->slice($page * self::PAGE_SIZE, self::PAGE_SIZE)
            ->map(function ($date) {
                return [
                    'id' => $date,
                    'title' => ArabicDateFormatter::format(Carbon::parse($date), false),
                    'description' => '',
                ];
            })
            ->values();

        if ($emptyDates->count() > (($page + 1) * self::PAGE_SIZE)) {
            $rows->push([
                'id' => 'more_days',
                'title' => 'أيام أخرى',
                'description' => '',
            ]);
        }

        SendMessage::list(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            'اختر يوماً لتبديل المواعيد',
            'اختر يوماً',
            $rows->toArray(),
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

        if ($selectedDate === 'more_days') {
            $conversation->update([
                'data' => array_merge($conversation->data ?? [], [
                    'replace_day_page' => ($conversation->data['replace_day_page'] ?? 0) + 1,
                ]),
            ]);

            return self::execute($conversation, $message);
        }

        if ($selectedDate) {
            return ReplaceTodayAppointments::execute($conversation, $selectedDate);

            $conversation->update([
                'state' => ConversationState::ADMIN_MENU,
                'data' => array_merge($conversation->data ?? [], [
                    'replace_day_page' => 0,
                ]),
            ]);
        }
    }
}