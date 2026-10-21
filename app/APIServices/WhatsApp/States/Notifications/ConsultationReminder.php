<?php

namespace App\APIServices\WhatsApp\States\Notifications;

use App\APIServices\WhatsApp\ConversationRouter;
use App\APIServices\WhatsApp\SendMessage;
use App\APIServices\WhatsApp\States\BookAppointment;
use App\APIServices\WhatsApp\States\MainMenu;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Prescription;
use App\Models\WhatsAppConversation;
use App\Services\Notifications\Channels\SendWhatsAppStatefulNotification as Channel;
use App\Services\Notifications\Modifications\MarkNotificationViewed;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

/**
 * The patient's conversation while a consultation reminder template waits for
 * an answer. The state the conversation was in before the reminder sits on
 * top of the call stack (pushed by the stateful channel).
 */
class ConsultationReminder
{
    public const BOOK_APPOINTMENT = 'consultation_book';

    public const LATER = 'consultation_later';

    public const VIEW_PRESCRIPTION = 'consultation_view_prescription';

    public const KEY_PRESCRIPTION_ID = 'consultation_prescription_id';

    public const KEY_CONSULTATION_DATE = 'consultation_date';

    /**
     * Payloads in the order the buttons are laid out in the Meta template.
     */
    private const BUTTONS = [
        0 => self::BOOK_APPOINTMENT,
        1 => self::VIEW_PRESCRIPTION,
        2 => self::LATER,
    ];

    private const LATER_REPLY = 'تمام، يمكنك حجز موعد الاستشارة في أي وقت من القائمة الرئيسية.';

    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        $account = DoctorWhatsAppAccount::findOrFail($conversation->doctor_whatsapp_account_id);
        $data = $conversation->data ?? [];

        return SendMessage::template(
            $account->phone_number_id,
            $account->access_token,
            $message['from'],
            $data[Channel::KEY_TEMPLATE_NAME],
            bodyParams: [
                'name' => ($data['name'] ?? null) ?: 'عزيزي المريض',
                'date' => ArabicDateFormatter::format(Carbon::parse($data[self::KEY_CONSULTATION_DATE]), includeTime: false),
            ],
            quickReplies: self::BUTTONS
        );
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message)
    {
        // Template quick replies arrive as "button", list/button replies as "interactive"
        $value = in_array($message['type'] ?? null, ['button', 'interactive'], true)
            ? ($message['value'] ?? null)
            : null;

        return match ($value) {
            self::BOOK_APPOINTMENT => self::book($conversation, $message),
            self::VIEW_PRESCRIPTION => self::viewPrescription($conversation, $message),
            self::LATER => self::later($conversation, $message),

            // Anything else is the patient carrying on with what they were
            // doing, so hand the message back to that state instead of
            // trapping them on the reminder.
            default => self::resume($conversation, $message),
        };
    }

    private static function book(WhatsAppConversation $conversation, array $message)
    {
        self::leave($conversation, restore: false);

        $conversation->update(['state' => ConversationState::BOOK_APPOINTMENT]);

        return BookAppointment::execute($conversation, $message);
    }

    private static function viewPrescription(WhatsAppConversation $conversation, array $message)
    {
        $prescription = Prescription::find(($conversation->data ?? [])[self::KEY_PRESCRIPTION_ID] ?? null);

        self::leave($conversation, restore: false);

        return MainMenu::showPrescription($conversation, $message, $prescription);
    }

    private static function later(WhatsAppConversation $conversation, array $message)
    {
        self::leave($conversation, restore: true);

        $account = DoctorWhatsAppAccount::findOrFail($conversation->doctor_whatsapp_account_id);

        return SendMessage::text($account->phone_number_id, $account->access_token, $message['from'], self::LATER_REPLY);
    }

    private static function resume(WhatsAppConversation $conversation, array $message)
    {
        self::leave($conversation, restore: true);

        return ConversationRouter::execute($conversation, $message);
    }

    /**
     * Resolves the notification and clears what the reminder added to the
     * conversation. The state pushed on entry is popped either way: restored
     * when the patient goes back to it, dropped when they move somewhere new.
     */
    private static function leave(WhatsAppConversation $conversation, bool $restore): void
    {
        $data = $conversation->data ?? [];

        MarkNotificationViewed::execute($data[Channel::KEY_NOTIFICATION_ID] ?? null);

        $previous = null;
        $callStack = $data['callStack'] ?? [];

        if (($data[Channel::KEY_PUSHED_STATE] ?? false) && $callStack !== []) {
            $previous = array_shift($callStack);
        }

        if ($callStack === []) {
            unset($data['callStack']);
        } else {
            $data['callStack'] = array_values($callStack);
        }

        unset(
            $data[Channel::KEY_PUSHED_STATE],
            $data[Channel::KEY_NOTIFICATION_ID],
            $data[Channel::KEY_TEMPLATE_NAME],
            $data[self::KEY_PRESCRIPTION_ID],
            $data[self::KEY_CONSULTATION_DATE],
        );

        // START is where a conversation with nothing to go back to begins
        $conversation->update([
            'state' => $restore && $previous ? ConversationState::from($previous) : ConversationState::START,
            'data' => $data,
        ]);
    }
}
