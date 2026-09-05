<?php

namespace App\Services\Notifications\Channels;

use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\Models\User;

class SendWhatsAppStatelessNotification
{
    public static function execute(User $sender, User $receiver, int $clinicId, string $title, string $body)
    {
        \Log::info("Creating whatsapp stateless notification for user {$receiver->name}");
        
        $account = DoctorWhatsAppAccount::where('doctor_id', $clinicId)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return;
        }

        $message = "*{$title}*\n\n{$body}";

        return SendMessage::text($account->phone_number_id, $account->access_token, $sender->phone, $message);
    }
}