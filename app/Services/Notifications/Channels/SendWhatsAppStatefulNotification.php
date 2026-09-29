<?php

namespace App\Services\Notifications\Channels;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\Models\DoctorWhatsAppAccount;
use App\Models\User;
use App\Models\WhatsAppConversation;

class SendWhatsAppStatefulNotification
{
    /**
     * Returns null when the clinic has no active WhatsApp account, meaning
     * the channel was not applicable rather than attempted and failed.
     *
     * The in-app notification row is threaded through the conversation data so
     * the patient's reply can be reconciled with it.
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

        $data = array_merge([
            'name' => $receiver->name,
            'template_name' => $type->templateName(),
        ], $data);

        if ($notificationId !== null) {
            $data['notification_id'] = $notificationId;
        }

        $conversation->update([
            'state' => $type->state(),
            'data' => $data,
            'last_activity_at' => now(),
        ]);

        $message = [
            'phone_number_id' => $account->phone_number_id,
            'from' => $receiver->phone,
            'type' => 'notification',
        ];

        return ExecutionRouter::execute($conversation, $message) !== false;
    }
}
