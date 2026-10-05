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

        // Only push on the first prompt. A re-prompt (free text while already in
        // the idle state) must not push IDLE_CHECK itself or a duplicate entry.
        if ($conversation->state !== ConversationState::IDLE_CHECK) {
            $data = $conversation->data ?? [];

            $callStack = $data['callStack'] ?? [];
            array_unshift($callStack, $conversation->state->value);

            $data['callStack'] = array_values($callStack);
            $data[self::KEY_PREVIOUS_STATE] = $conversation->state->value;
            $data[self::KEY_PROMPTED_AT] = now()->toDateTimeString();

            $update['data'] = $data;
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

        // The state pushed in execute() is on top of the stack.
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
        // Drop the state we pushed, so it doesn't linger in the stack after leaving.
        $data = $conversation->data ?? [];
        $callStack = $data['callStack'] ?? [];

        if (isset($data[self::KEY_PREVIOUS_STATE]) && count($callStack) > 0) {
            array_shift($callStack);
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

        $conversation->update(['data' => $data]);

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