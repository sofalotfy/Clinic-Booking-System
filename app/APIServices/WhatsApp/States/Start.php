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

            $conversation->pushData([
                'name' => $conversation->user->name ?? '',
            ]);

            return $conversation->startFlow(
                ConversationState::IDLE,
                $message
            );
        }

        $conversation->pushData([
            'name' => $conversation->user->name ?? '',
        ]);

        return $conversation->startFlow(
            ConversationState::MAIN_MENU,
            $message
        );
    }
}