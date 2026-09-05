<?php

namespace App\Enums;

enum NotificationEnum
{
    case PATIENT_APPOINTMENT_BOOKED;
    case PATIENT_APPOINTMENT_RESCHEDULED;
    case PATIENT_APPOINTMENT_CANCEL;
    case DOCTOR_APPOINTMENT_BOOKED;
    case DOCTOR_APPOINTMENT_RESCHEDULED;
    case DOCTOR_APPOINTMENT_CANCEL;

    public function type(): self
    {
        return $this;
    }

    public function label(): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'Patient Booked Appointment',
            self::PATIENT_APPOINTMENT_RESCHEDULED => 'Patient Rescheduled Appointment',
            self::PATIENT_APPOINTMENT_CANCEL => 'Patient Cancelled Appointment',
            self::DOCTOR_APPOINTMENT_BOOKED => 'Doctor Booked Appointment',
            self::DOCTOR_APPOINTMENT_RESCHEDULED => 'Doctor Rescheduled Appointment',
            self::DOCTOR_APPOINTMENT_CANCEL => 'Doctor Cancelled Appointment',
        };
    }

    public function permission(): ?string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED =>
                'patient_appointment_booked_notifications',

            self::PATIENT_APPOINTMENT_RESCHEDULED =>
                'patient_appointment_rescheduled_notifications',

            self::PATIENT_APPOINTMENT_CANCEL =>
                'patient_appointment_cancel_notifications',

            self::DOCTOR_APPOINTMENT_BOOKED =>
                'doctor_appointment_booked_notifications',

            self::DOCTOR_APPOINTMENT_RESCHEDULED =>
                'doctor_appointment_rescheduled_notifications',

            self::DOCTOR_APPOINTMENT_CANCEL =>
                'doctor_appointment_cancel_notifications',
        };
    }

    public function notifiesPatient(): bool
    {
        return match ($this) {
            self::DOCTOR_APPOINTMENT_BOOKED,
            self::DOCTOR_APPOINTMENT_RESCHEDULED,
            self::DOCTOR_APPOINTMENT_CANCEL => true,

            default => false,
        };
    }

    public function notifiesClinic(): bool
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED,
            self::PATIENT_APPOINTMENT_RESCHEDULED,
            self::PATIENT_APPOINTMENT_CANCEL => true,

            default => false,
        };
    }

    public function title(array $data = []): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED =>
                'New Appointment Booked',

            self::PATIENT_APPOINTMENT_RESCHEDULED =>
                'Appointment Rescheduled',

            self::PATIENT_APPOINTMENT_CANCEL =>
                'Appointment Cancelled',

            self::DOCTOR_APPOINTMENT_BOOKED =>
                'New Appointment Booked',

            self::DOCTOR_APPOINTMENT_RESCHEDULED =>
                'Appointment Rescheduled',

            self::DOCTOR_APPOINTMENT_CANCEL =>
                'Appointment Cancelled',
        };
    }

    public function body(array $data = []): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED =>
                "{$data['patient_name']} booked an appointment on {$data['date']}.",

            self::PATIENT_APPOINTMENT_RESCHEDULED =>
                "{$data['patient_name']} rescheduled their appointment to {$data['date']}.",

            self::PATIENT_APPOINTMENT_CANCEL =>
                "{$data['patient_name']} cancelled their appointment on {$data['date']}.",

            self::DOCTOR_APPOINTMENT_BOOKED =>
                "Your appointment was booked for {$data['date']}.",

            self::DOCTOR_APPOINTMENT_RESCHEDULED =>
                "Your appointment was rescheduled to {$data['date']}.",

            self::DOCTOR_APPOINTMENT_CANCEL =>
                "Your appointment on {$data['date']} was cancelled.",
        };
    }

    public function link(array $data = []): ?string
    {
        return match ($this) {
            default => '/',
        };
    }

    public function state(): ?ConversationState
    {
        return match ($this) {
            self::DOCTOR_APPOINTMENT_BOOKED =>
                ConversationState::DOCTOR_APPOINTMENT_BOOKED,
            self::DOCTOR_APPOINTMENT_RESCHEDULED =>
                ConversationState::DOCTOR_APPOINTMENT_RESCHEDULE,
            default => null,
        };
    }
}