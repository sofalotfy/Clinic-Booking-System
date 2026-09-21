<?php

namespace App\Services\Notifications\Channels;

use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\Models\User;

class SendWhatsAppStatelessNotification
{
    public const DEFAULT_LANGUAGE = 'ar_EG';

    /**
     * @param string $templateName  technical template name, e.g. "doctor_appointment_cancel"
     * @param array  $params        named variables, e.g. ['name' => 'Ahmed', 'date' => '...']
     */
    public static function execute(
        User $sender,
        User $receiver,
        int $clinicId,
        string $templateName,
        array $params = [],
        string $language = self::DEFAULT_LANGUAGE
    ) {
        \Log::info("Creating whatsapp template notification '{$templateName}' for user {$receiver->name}");

        $account = DoctorWhatsAppAccount::where('doctor_id', $clinicId)
            ->where('is_active', true)
            ->first();

        if (! $account) {
            return;
        }

        return SendMessage::template(
            $account->phone_number_id,
            $account->access_token,
            $receiver->phone,
            $templateName,
            $language,
            bodyParams: $params
        );
    }
}