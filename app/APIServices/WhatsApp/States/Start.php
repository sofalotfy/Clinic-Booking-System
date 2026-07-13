<?php

namespace App\APIServices\WhatsApp\States;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;

class Start
{
    public static function execute(WhatsAppConversation $conversation, array $message)
    {
        // Reset the conversation
        $conversation->update([
            'state' => ConversationState::MAIN_MENU,
            'step'  => null,
            'data'  => ['name' => $conversation->patient->user->name],
        ]);

        return MainMenu::execute(
            $conversation,
            $message
        );
    }
}