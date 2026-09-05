<?php

namespace App\Enums;

enum NotificationEnum
{
    case PATIENT_APPOINTMENT_BOOKED;
    case PATIENT_APPOINTMENT_RESCHEDULED;

    public function type(): self
    {
        return $this;
    }

    public function label(): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'Patient Booked Appointment',
            self::PATIENT_APPOINTMENT_RESCHEDULED => 'Patient Rescheduled Appointment',
            default => $this->name,
        };
    }

    public function permission(): ?string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'patient_appointment_booked_notifications',
            self::PATIENT_APPOINTMENT_RESCHEDULED => 'patient_appointment_rescheduled_notifications',
            default => null,
        };
    }

    public function notifiesPatient(): bool
    {
        return match ($this) {
            default => false,
        };
    }

    public function notifiesClinic(): bool
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED, self::PATIENT_APPOINTMENT_RESCHEDULED => true,
            default => false,
        };
    }

    public function  title(array $data = []): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'New Appointment Booked',
            self::PATIENT_APPOINTMENT_RESCHEDULED => 'Appointment Rescheduled',
            default => '',
        };
    }

    public function body(array $data = []): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => "{$data['patient_name']} booked an appointment on {$data['date']}.",
            self::PATIENT_APPOINTMENT_RESCHEDULED => "{$data['patient_name']} rescheduled their appointment to {$data['date']}.",
            default => '',
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
            // self::APPOINTMENT_RESCHEDULED => ConversationState::CONFIRM_RESHEDULE,
            default => null,
        };
    }
}