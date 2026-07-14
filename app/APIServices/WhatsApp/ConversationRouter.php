<?php

namespace App\APIServices\WhatsApp;

use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use App\APIServices\WhatsApp\States\Start;
use App\APIServices\WhatsApp\States\MainMenu;
use App\APIServices\WhatsApp\States\CancelState;
use App\APIServices\WhatsApp\States\InfoInquiry;
use App\APIServices\WhatsApp\States\InfoConfirmation;
use App\APIServices\WhatsApp\States\BookAppointment;

class ConversationRouter
{
    public static function execute( WhatsAppConversation $conversation, array $message) 
    {
        return match ($conversation->state) {

            null => Start::execute($conversation, $message),

            ConversationState::MAIN_MENU =>
                MainMenu::handleResponse($conversation, $message),

            ConversationState::BOOK_APPOINTMENT =>
                BookAppointment::handleResponse($conversation, $message),

            ConversationState::INFO_INQUIRY =>
                InfoInquiry::handleResponse($conversation, $message),

            ConversationState::INFO_CONFIRMATION =>
                InfoConfirmation::handleResponse($conversation, $message),

            // ConversationState::RESCHEDULE =>
            //     RescheduleState::execute($conversation, $message),

            // ConversationState::CANCEL =>
            //     CancelState::execute($conversation, $message),

            default =>
                Start::execute($conversation, $message),
        };
    }
}