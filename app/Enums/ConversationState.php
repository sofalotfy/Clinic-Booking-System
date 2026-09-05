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
    case EMERGENCY_CASE = 'emergency_case';
    case EMERGENCY_CASE_IN_HOME = 'emergency_case_in_home';
    case EMERGENCY_CASE_IN_HOSPITAL = 'emergency_case_in_hospital';
    case AI = 'AI';
    case IDLE = 'idle';

    //Notification Status
    case DOCTOR_APPOINTMENT_BOOKING = 'doctor_appointment_booking';
    case DOCTOR_APPOINTMENT_RESCHEDULE = 'doctor_appointment_reschedule';
}