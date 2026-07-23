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
use App\APIServices\WhatsApp\States\BookSlot;
use App\APIServices\WhatsApp\States\ConfirmBooking;
use App\APIServices\WhatsApp\States\CancelAppointment;
use App\APIServices\WhatsApp\States\ConfirmReshedule;

class ExecutionRouter
{
    public static function execute( WhatsAppConversation $conversation, array $message = null) 
    {
        return match ($conversation->state) {

            null => Start::execute($conversation, $message),

            ConversationState::MAIN_MENU =>
                MainMenu::execute($conversation, $message),

            ConversationState::BOOK_APPOINTMENT =>
                BookAppointment::execute($conversation, $message),

            ConversationState::INFO_INQUIRY =>
                InfoInquiry::execute($conversation, $message),

            ConversationState::INFO_CONFIRMATION =>
                InfoConfirmation::execute($conversation, $message),

            ConversationState::BOOK_SLOT =>
                BookSlot::execute($conversation, $message),

            ConversationState::CONFIRM_BOOKING =>
                ConfirmBooking::execute($conversation, $message),

            ConversationState::CANCEL_APPOINTMENT =>
                CancelAppointment::execute($conversation, $message),

            ConversationState::CONFIRM_RESHEDULE =>
                ConfirmReshedule::execute($conversation, $message),

            default =>
                Start::execute($conversation, $message),
        };
    }
}