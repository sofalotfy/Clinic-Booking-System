<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;

class Start
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        // Route non-patient users to IdleState
        if (!$conversation->user || !$conversation->user->isPatient()) {
            $conversation->update([
                'state' => ConversationState::IDLE,
                'step'  => null,
                'data'  => ['name' => $conversation->user->name ?? ''],
            ]);

            return IdleState::execute(
                $conversation,
                $message
            );
        }

        // Reset the conversation
        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
            'step'  => null,
            'data'  => ['name' => $conversation->user->name],
        ]);

        return MainMenu::execute(
            $conversation,
            $message
        );
    }
}