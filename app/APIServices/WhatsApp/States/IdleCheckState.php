<?php

namespace App\APIServices\WhatsApp\States;

use App\APIServices\WhatsApp\SendMessage;
use App\Enums\ConversationState;
use App\Models\DoctorWhatsAppAccount;
use App\Models\WhatsAppConversation;

class IdleCheckState
{
    public const CONTINUE_CONVERSATION = 'continue_conversation';

    public const END_CONVERSATION = 'end_conversation';

    public const BACK_TO_MAIN_MENU = 'back_to_mainmenu';

    public const KEY_PREVIOUS_STATE = 'idle_previous_state';

    public const KEY_PROMPTED_AT = 'idle_prompted_at';

    private const PROMPT_QUESTION = 'هل ترغب بإنهاء المحادثة ؟';

    public static function execute(?WhatsAppConversation $conversation, ?array $message = null)
    {
        $account = $conversation->doctorWhatsAppAccount
            ?? DoctorWhatsAppAccount::findOrFail($conversation->doctor_whatsapp_account_id);

        $text = self::buildPromptText($conversation);

        $sent = SendMessage::buttons(
            $account->phone_number_id,
            $account->access_token,
            $conversation->phone_number,
            $text,
            self::buttons()
        );

        if ($sent === false) {
            \Log::error('IDLE PROMPT FAILED conversation '.$conversation->id);

            return false;
        }

        $update = ['state' => ConversationState::IDLE_CHECK];

        // A re-prompt (free text while already in the prompt state) must keep the
        // original snapshot, otherwise the FR-014 resume fallback would be lost.
        if ($conversation->state !== ConversationState::IDLE_CHECK) {
            $update['data'] = array_merge(
                $conversation->data ?? [],
                [
                    self::KEY_PREVIOUS_STATE => $conversation->state->value,
                    self::KEY_PROMPTED_AT => now()->toDateTimeString(),
                ]
            );
        }

        $conversation->update($update);

        return $sent;
    }

    public static function handleResponse(WhatsAppConversation $conversation, array $message): WhatsAppConversation
    {
        if (($message['type'] ?? null) !== 'interactive') {
            self::execute($conversation, $message);

            return $conversation;
        }

        return match ($message['value'] ?? null) {
            self::CONTINUE_CONVERSATION => self::resume($conversation, $message),

            self::END_CONVERSATION,
            self::BACK_TO_MAIN_MENU => self::returnToMenu($conversation, $message),

            default => self::reprompt($conversation, $message),
        };
    }

    private static function buildPromptText(WhatsAppConversation $conversation): string
    {
        $name = trim((string) ($conversation->user?->name ?? ''));

        if ($name === '') {
            return self::PROMPT_QUESTION;
        }

        return "اهلا {$name}\n".self::PROMPT_QUESTION;
    }

    private static function buttons(): array
    {
        return [
            [
                'id' => self::END_CONVERSATION,
                'title' => 'إنهاء المحادثة',
            ],
            [
                'id' => self::CONTINUE_CONVERSATION,
                'title' => 'استكمال المحادثة',
            ],
            [
                'id' => self::BACK_TO_MAIN_MENU,
                'title' => 'القائمة الرئيسية',
            ],
        ];
    }

    private static function resume(WhatsAppConversation $conversation, array $message): WhatsAppConversation
    {
        $data = $conversation->data ?? [];

        $callStack = $data['callStack'] ?? [];
        $state = count($callStack) > 0
            ? array_shift($callStack)
            : ($data[self::KEY_PREVIOUS_STATE] ?? null);

        if ($state === null) {
            return self::reprompt($conversation, $message);
        }

        if ($callStack === []) {
            unset($data['callStack']);
        } else {
            $data['callStack'] = array_values($callStack);
        }

        unset(
            $data[self::KEY_PREVIOUS_STATE],
            $data[self::KEY_PROMPTED_AT]
        );

        $conversation->update([
            'state' => ConversationState::from($state),
            'step' => null,
            'data' => $data,
            'last_activity_at' => now(),
            'expires_at' => now()->addMinutes(10),
        ]);

        return $conversation;
    }

    private static function returnToMenu(WhatsAppConversation $conversation, array $message): WhatsAppConversation
    {
        Start::execute(
            $conversation,
            ['from' => $conversation->phone_number]
        );

        return $conversation;
    }

    private static function reprompt(WhatsAppConversation $conversation, array $message): WhatsAppConversation
    {
        self::execute($conversation, $message);

        return $conversation;
    }
}
