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
use App\APIServices\WhatsApp\States\EmergencyCase;
use App\APIServices\WhatsApp\States\AI;

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

            ConversationState::BOOK_SLOT =>
                BookSlot::handleResponse($conversation, $message),

            ConversationState::CONFIRM_BOOKING =>
                ConfirmBooking::handleResponse($conversation, $message),

            ConversationState::CANCEL_APPOINTMENT =>
                CancelAppointment::handleResponse($conversation, $message),

            ConversationState::CONFIRM_RESHEDULE =>
                ConfirmReshedule::handleResponse($conversation, $message),

            ConversationState::EMERGENCY_CASE =>
                EmergencyCase::handleResponse($conversation, $message),

            ConversationState::AI =>
                AI::handleResponse($conversation, $message),

            default =>
                Start::execute($conversation, $message),
        };
    }
}