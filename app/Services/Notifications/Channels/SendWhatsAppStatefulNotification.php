<?php

namespace App\Services\Notifications\Channels;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\Models\DoctorWhatsAppAccount;
use App\Models\User;
use App\Models\WhatsAppConversation;

class SendWhatsAppStatefulNotification
{
    public static function execute(User $sender, User $receiver, int $clinicId, $type, array $data = [])
    {
        \Log::info("Creating whatsapp stateful notification for user {$receiver->name}");
        
        $account = DoctorWhatsAppAccount::where('doctor_id', $clinicId)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return;
        }

        //$conversation = WhatsAppConversation::where('user_id', $receiver->id)->first();
        $conversation = WhatsAppConversation::where('user_id', $receiver->id)->first();

        if (! $conversation) {
            $conversation = WhatsAppConversation::create([
                'doctor_whatsapp_account_id' => $account->id,
                'phone_number' => $receiver->phone,
                'user_id' => $receiver->id,
            ]);
        }

        $conversation->update([
            'state' => $type->state(),
            'data' => array_merge([
                'name' => $receiver->name,
            ], $data),
            'last_activity_at' => now(),
        ]);

        ExecutionRouter::execute($conversation);
    }
}