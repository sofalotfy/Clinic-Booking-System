<?php

namespace App\Enums;

enum BookingStep: string
{
    case CHOOSE_DOCTOR = 'choose_doctor';
    case CHOOSE_DATE = 'choose_date';
    case CHOOSE_TIME = 'choose_time';
    case CONFIRM = 'confirm';
}