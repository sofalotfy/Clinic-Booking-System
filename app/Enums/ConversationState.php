<?php

namespace App\Enums;

enum ConversationState: string
{
    case START = 'start';
    case MAIN_MENU = 'main_menu';
    case BOOK_APPOINTMENT = 'book_appointment';
    case RESCHEDULE_APPOINTMENT = 'reschedule_appointment';
    case CANCEL_APPOINTMENT = 'cancel_appointment';
    Case INFO_INQUIRY = 'info_inquiry';
    Case INFO_CONFIRMATION = 'info_confirmation';
    Case BOOK_SLOT = 'book_slot';
}