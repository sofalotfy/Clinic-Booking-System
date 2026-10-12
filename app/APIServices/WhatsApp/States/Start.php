<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;

class Start
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        // Only the clinic's registered notifications phone gets the staff menu.
        if (self::isClinicNotificationsPhone($conversation)) {
            $conversation->update([
                'state' => ConversationState::ADMIN_MENU,
                'step' => null,
                'data' => ['name' => $conversation->user->name ?? ''],
            ]);

            return AdminMenu::execute(
                $conversation,
                $message
            );
        }

        // Everyone else gets the patient menu
        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
            'step' => null,
            'data' => ['name' => $conversation->user->name],
        ]);

        return MainMenu::execute(
            $conversation,
            $message
        );
    }

    private static function isClinicNotificationsPhone(WhatsAppConversation $conversation): bool
    {
        $clinicPhone = DoctorWhatsAppAccount::find($conversation->doctor_whatsapp_account_id)
            ?->doctor
            ?->clinic
            ?->notifications_phone;

        if (! $clinicPhone) {
            return false;
        }

        return self::normalizePhone($clinicPhone) === self::normalizePhone($conversation->phone_number);
    }

    private static function normalizePhone(?string $phone): string
    {
        // WhatsApp sends numbers as plain digits, while the clinic number may be
        // stored with a +, spaces or a 00 international prefix.
        return ltrim(preg_replace('/\D+/', '', (string) $phone), '0');
    }
}
