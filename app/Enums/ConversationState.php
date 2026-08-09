<?php

namespace App\Enums;

enum ConversationState: string
{
    case START = 'start';
    case MAIN_MENU = 'main_menu';
    case BOOK_APPOINTMENT = 'book_appointment';
    case RESCHEDULE_APPOINTMENT = 'reschedule_appointment';
    case CANCEL_APPOINTMENT = 'cancel_appointment';
    case INFO_INQUIRY = 'info_inquiry';
    case INFO_CONFIRMATION = 'info_confirmation';
    case BOOK_SLOT = 'book_slot';
    case CONFIRM_BOOKING = 'confirm_booking';
    case CONFIRM_RESHEDULE = 'confirm_reshedule';
    case AI    =   'AI';
}