<?php

namespace App\APIServices\WhatsApp;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\Start;
use App\APIServices\WhatsApp\States\MainMenu;
use App\APIServices\WhatsApp\States\BookingState;
use App\APIServices\WhatsApp\States\RescheduleState;
use App\APIServices\WhatsApp\States\CancelState;

class ConversationRouter
{
    public static function execute( WhatsAppConversation $conversation, array $message) 
    {
        return match ($conversation->state) {

            null => Start::execute($conversation, $message),

            ConversationState::MAIN_MENU =>
                MainMenu::handleResponse($conversation, $message),

            // ConversationState::BOOKING =>
            //     BookingState::execute($conversation, $message),

            // ConversationState::RESCHEDULE =>
            //     RescheduleState::execute($conversation, $message),

            // ConversationState::CANCEL =>
            //     CancelState::execute($conversation, $message),

            default =>
                Start::execute($conversation, $message),
        };
    }
}