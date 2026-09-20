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
    // WhatsApp lists allow 10 rows max: 9 days + 1 "more" row.
    private const PAGE_SIZE = 9;

    private const PAGE_KEY = 'replace_day_page';

    /**
     * Entry point: called when the doctor enters this state.
     * Always starts from the first page.
     */
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $conversation->update([
            'state' => ConversationState::CHOOSE_REPLACE_DAY,
            'data' => array_merge($conversation->data ?? [], [
                self::PAGE_KEY => 0,
            ]),
        ]);

        return self::render($conversation);
    }

    public static function handleResponse($conversation, $message)
    {
        // This state only accepts interactive list replies
        if ($message['type'] !== 'interactive') {
            return self::render($conversation);
        }

        $selected = $message['value'] ?? null;

        if (! $selected) {
            return self::render($conversation);
        }

        // Doctor asked for the next page
        if ($selected === 'more_days') {
            $conversation->update([
                'data' => array_merge($conversation->data ?? [], [
                    self::PAGE_KEY => ($conversation->data[self::PAGE_KEY] ?? 0) + 1,
                ]),
            ]);

            return self::render($conversation);
        }

        // Validate that the chosen day is still empty
        [$account, $doctor] = self::context($conversation);

        $validDates = self::emptyDays($doctor)->all();

        if (! in_array((string) $selected, $validDates, true)) {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $conversation->phone_number,
                "نأسف .. هذا اليوم لم يعد متاحاً\nمن فضلك اختر يوماً آخر"
            );

            return self::render($conversation);
        }

        ReplaceTodayAppointments::execute($conversation, $selected);

        $conversation->update([
            'state' => ConversationState::ADMIN_MENU,
            'data' => collect($conversation->data ?? [])
                ->except(self::PAGE_KEY)
                ->all(),
        ]);
    }

    /**
     * Send the current page of empty days.
     */
    private static function render(WhatsAppConversation $conversation)
    {
        [$account, $doctor] = self::context($conversation);

        $page = $conversation->data[self::PAGE_KEY] ?? 0;
        $days = self::emptyDays($doctor);

        // Nothing to choose from: an empty list would be rejected by WhatsApp.
        if ($days->isEmpty()) {
            SendMessage::text(
                $account->phone_number_id,
                $account->access_token,
                $conversation->phone_number,
                'لا توجد أيام فارغة متاحة حالياً'
            );

            $conversation->update([
                'state' => ConversationState::ADMIN_MENU,
                'data' => collect($conversation->data ?? [])
                    ->except(self::PAGE_KEY)
                    ->all(),
            ]);

            return;
        }

        $rows = $days
            ->slice($page * self::PAGE_SIZE, self::PAGE_SIZE)
            ->map(fn ($date) => [
                'id' => $date,
                'title' => ArabicDateFormatter::format(Carbon::parse($date), false),
                'description' => '',
            ])
            ->values();

        // Page became invalid because the available days changed
        if ($rows->isEmpty()) {
            $conversation->update([
                'data' => array_merge($conversation->data ?? [], [
                    self::PAGE_KEY => 0,
                ]),
            ]);

            return self::render($conversation);
        }

        if ($days->count() > (($page + 1) * self::PAGE_SIZE)) {
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

    /**
     * @return array{0: DoctorWhatsAppAccount, 1: \App\Models\Doctor}
     */
    private static function context(WhatsAppConversation $conversation): array
    {
        $account = DoctorWhatsAppAccount::findOrFail(
            $conversation->doctor_whatsapp_account_id
        );

        return [$account, $account->doctor];
    }

    /**
     * Empty days as a list of date strings.
     */
    private static function emptyDays($doctor)
    {
        return collect(GetEmptyDays::execute($doctor->user, $doctor->id))
            ->map(fn ($date) => (string) $date)
            ->values();
    }
}