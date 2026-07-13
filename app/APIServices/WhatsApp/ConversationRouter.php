<?php

namespace App\APIServices\WhatsApp;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\StartState;
use App\APIServices\WhatsApp\States\MainMenuState;
use App\APIServices\WhatsApp\States\BookingState;
use App\APIServices\WhatsApp\States\RescheduleState;
use App\APIServices\WhatsApp\States\CancelState;

class ConversationRouter
{
    public static function execute( WhatsAppConversation $conversation, array $message) 
    {
        return match ($conversation->state) {

            null => StartState::execute($conversation, $message),

            ConversationState::MAIN_MENU =>
                MainMenuState::execute($conversation, $message),

            // ConversationState::BOOKING =>
            //     BookingState::execute($conversation, $message),

            // ConversationState::RESCHEDULE =>
            //     RescheduleState::execute($conversation, $message),

            // ConversationState::CANCEL =>
            //     CancelState::execute($conversation, $message),

            default =>
                StartState::execute($conversation, $message),
        };
    }
}