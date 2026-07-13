<?php

namespace App\Enums;

enum ConversationState: string
{
    case START = 'start';
    case MAIN_MENU = 'main_menu';
    case BOOKING = 'booking';
    case RESCHEDULE = 'reschedule';
    case CANCEL = 'cancel';
    case PROFILE = 'profile';
    case FINISHED = 'finished';
}