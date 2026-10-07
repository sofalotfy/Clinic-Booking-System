<?php

namespace App\Enums;

enum ConversationState: string
{
    case START = 'start';
    case MAIN_MENU = 'main_menu';
    case MANAGE_APPOINTMENT = 'manage_appointment';
    case SUBMIT_FEEDBACK = 'submit_feedback';
    case BOOK_APPOINTMENT = 'book_appointment';
    case CANCEL_APPOINTMENT = 'cancel_appointment';
    case INFO_INQUIRY = 'info_inquiry';
    case INFO_CONFIRMATION = 'info_confirmation';
    case BOOK_SLOT = 'book_slot';
    case CONFIRM_BOOKING = 'confirm_booking';
    case EMERGENCY_CASE = 'emergency_case';
    case EMERGENCY_CASE_IN_HOME = 'emergency_case_in_home';
    case EMERGENCY_CASE_IN_HOSPITAL = 'emergency_case_in_hospital';
    case AI = 'AI';
    case ADMIN_MENU = 'admin_menu';
    case IDLE_CHECK = 'idle_check';
    case CHOOSE_REPLACE_DAY = 'choose_replace_day';

    // Notification Status
    case DOCTOR_APPOINTMENT_BOOKING = 'doctor_appointment_booking';
    case DOCTOR_APPOINTMENT_RESCHEDULE = 'doctor_appointment_reschedule';

    /**
     * States that represent an entry point or a menu rather than productive
     * work. A conversation sitting in one of these is not stuck in anything,
     * so it is never a candidate for the idle prompt.
     */
    public const array ROUTING_STATES = [
        self::START,
        self::MAIN_MENU,
        self::ADMIN_MENU,
        self::IDLE_CHECK,
        self::DOCTOR_APPOINTMENT_BOOKING,
        self::DOCTOR_APPOINTMENT_RESCHEDULE,
    ];

    /**
     * Every conversation state that counts as an in-progress flow.
     *
     * Computed as the complement of ROUTING_STATES so that a state added later
     * is picked up automatically. Do not replace this with a hard-coded list:
     * a literal list fails silently whenever a new flow is introduced, because
     * that flow would simply never be prompted.
     *
     * @return array<int, self>
     */
    public static function active(): array
    {
        return array_values(
            array_filter(
                self::cases(),
                fn (self $state) => ! in_array($state, self::ROUTING_STATES, true)
            )
        );
    }
}
