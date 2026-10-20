<?php

namespace App\Services\Notifications\Channels;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\Models\DoctorWhatsAppAccount;
use App\Models\User;
use App\Models\WhatsAppConversation;

/**
 * Sends a WhatsApp notification the patient answers with buttons. The
 * conversation is moved into the notification's state so the reply is routed
 * back to it, and the state it was in is pushed onto the call stack so the
 * conversation can be resumed exactly where the patient left it.
 */
class SendWhatsAppStatefulNotification
{
    /**
     * Set to true in the conversation data when a state was pushed, so the
     * notification state knows whether there is something of ours to pop.
     */
    public const KEY_PUSHED_STATE = 'notification_pushed_state';

    public const KEY_NOTIFICATION_ID = 'notification_id';

    public const KEY_TEMPLATE_NAME = 'template_name';

    /**
     * Returns null when the clinic has no active WhatsApp account, meaning
     * the channel was not applicable rather than attempted and failed.
     */
    public static function execute(
        User $sender,
        User $receiver,
        int $clinicId,
        $type,
        array $data = [],
        ?int $notificationId = null
    ): ?bool {
        \Log::info("Creating whatsapp stateful notification for user {$receiver->name}");

        $account = DoctorWhatsAppAccount::where('doctor_id', $clinicId)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            \Log::info("No active whatsapp account for clinic {$clinicId}, skipping stateful notification");

            return null;
        }

        $conversation = WhatsAppConversation::firstOrCreate(
            [
                'doctor_whatsapp_account_id' => $account->id,
                'phone_number' => $receiver->phone,
            ],
            ['user_id' => $receiver->id]
        );

        // row existed before the user was linked
        if ($conversation->user_id !== $receiver->id) {
            $conversation->update(['user_id' => $receiver->id]);
        }

        $conversation->update([
            'state' => $type->state(),
            'data' => self::conversationData($conversation, $receiver, $type, $data, $notificationId),
            'last_activity_at' => now(),
        ]);

        $message = [
            'phone_number_id' => $account->phone_number_id,
            'from' => $receiver->phone,
            'type' => 'notification',
        ];

        return ExecutionRouter::execute($conversation, $message) !== false;
    }

    /**
     * Keeps what the conversation already holds (its call stack included) and
     * pushes the current state, unless it is already this notification's
     * state: a second reminder must not bury the state the patient came from.
     */
    private static function conversationData(WhatsAppConversation $conversation, User $receiver, $type, array $data, ?int $notificationId): array
    {
        $current = $conversation->data ?? [];
        $alreadyWaiting = $conversation->state === $type->state();

        if (! $alreadyWaiting) {
            $current[self::KEY_PUSHED_STATE] = $conversation->state !== null;

            if ($conversation->state !== null) {
                $callStack = $current['callStack'] ?? [];
                array_unshift($callStack, $conversation->state->value);
                $current['callStack'] = array_values($callStack);
            }
        }

        return array_merge($current, [
            'name' => $receiver->name,
            self::KEY_TEMPLATE_NAME => $type->templateName(),
            self::KEY_NOTIFICATION_ID => $notificationId,
        ], $data);
    }
}
