<?php

namespace App\Enums;

use App\Support\ArabicDateFormatter;
use Carbon\Carbon;

enum NotificationEnum
{
    case PATIENT_APPOINTMENT_BOOKED;
    case PATIENT_APPOINTMENT_RESCHEDULED;
    case PATIENT_APPOINTMENT_CANCEL;
    case DOCTOR_APPOINTMENT_BOOKED;
    case DOCTOR_APPOINTMENT_RESCHEDULED;
    case DOCTOR_APPOINTMENT_CANCEL;
    case PATIENT_DAY_APPOINTMENT_REMINDER;
    case PATIENT_FEEDBACK;
    case DOCTOR_DAY_APPOINTMENTS;
    case PATIENT_CONSULTATION_REMINDER_WEEK;
    case PATIENT_CONSULTATION_REMINDER_DAY;

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
            self::PATIENT_DAY_APPOINTMENT_REMINDER => 'Patient Day Appointment Reminder',

            self::PATIENT_FEEDBACK => 'Patient Sent Feedback',

            self::DOCTOR_DAY_APPOINTMENTS => 'Doctor Day Appointments',

            self::PATIENT_CONSULTATION_REMINDER_WEEK => 'Patient Consultation Reminder (1 week)',
            self::PATIENT_CONSULTATION_REMINDER_DAY => 'Patient Consultation Reminder (1 day)',
        };
    }

    public function permission(): ?string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'patient_appointment_booked_notifications',

            self::PATIENT_APPOINTMENT_RESCHEDULED => 'patient_appointment_rescheduled_notifications',

            self::PATIENT_APPOINTMENT_CANCEL => 'patient_appointment_cancel_notifications',

            self::DOCTOR_APPOINTMENT_BOOKED => 'doctor_appointment_booked_notifications',

            self::DOCTOR_APPOINTMENT_RESCHEDULED => 'doctor_appointment_rescheduled_notifications',

            self::DOCTOR_APPOINTMENT_CANCEL => 'doctor_appointment_cancel_notifications',

            self::PATIENT_DAY_APPOINTMENT_REMINDER => 'patient_day_appointment_reminder_notifications',

            self::PATIENT_FEEDBACK => 'patient_feedback_notifications',

            self::DOCTOR_DAY_APPOINTMENTS => 'doctor_day_appointments_summary_notifications',

            self::PATIENT_CONSULTATION_REMINDER_WEEK => 'patient_consultation_reminder_week_notifications',

            self::PATIENT_CONSULTATION_REMINDER_DAY => 'patient_consultation_reminder_day_notifications',
        };
    }

    public function notifiesPatient(): bool
    {
        return match ($this) {
            self::DOCTOR_APPOINTMENT_BOOKED,
            self::DOCTOR_APPOINTMENT_RESCHEDULED,
            self::DOCTOR_APPOINTMENT_CANCEL,
            self::PATIENT_DAY_APPOINTMENT_REMINDER,
            self::PATIENT_CONSULTATION_REMINDER_WEEK,
            self::PATIENT_CONSULTATION_REMINDER_DAY => true,

            default => false,
        };
    }

    public function notifiesClinic(): bool
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED,
            self::PATIENT_APPOINTMENT_RESCHEDULED,
            self::PATIENT_APPOINTMENT_CANCEL,
            self::PATIENT_FEEDBACK,
            self::DOCTOR_DAY_APPOINTMENTS => true,

            default => false,
        };
    }

    public function templateName(array $data = []): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'appointment_booking_by_patient',

            self::PATIENT_APPOINTMENT_RESCHEDULED => 'appointment_reschedule_by_patient',

            self::PATIENT_APPOINTMENT_CANCEL => 'appointment_cancelation_by_patient',

            self::DOCTOR_APPOINTMENT_BOOKED => 'appointment_creation_by_doctor',

            self::DOCTOR_APPOINTMENT_RESCHEDULED => 'appointment_reschedule_by_doctor',

            self::DOCTOR_APPOINTMENT_CANCEL => 'appointment_cancelation_by_doctor',

            self::PATIENT_DAY_APPOINTMENT_REMINDER => 'patient_day_appointment_reminder',

            self::PATIENT_FEEDBACK => 'feedback_by_patient',

            self::DOCTOR_DAY_APPOINTMENTS => 'today_appointments_summary_doctor',

            self::PATIENT_CONSULTATION_REMINDER_WEEK => 'consultation_week_reminder',

            self::PATIENT_CONSULTATION_REMINDER_DAY => 'consultation_day_reminder',
        };
    }

    public function title(array $data = []): string
    {
        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => 'حجز جديد',

            self::PATIENT_APPOINTMENT_RESCHEDULED => 'تعديل موعد حجز',

            self::PATIENT_APPOINTMENT_CANCEL => 'إلغاء موعد حجز',

            self::DOCTOR_APPOINTMENT_BOOKED => 'حجز جديد',

            self::DOCTOR_APPOINTMENT_RESCHEDULED => 'تعديل موعد حجز',

            self::DOCTOR_APPOINTMENT_CANCEL => 'إلغاء موعد حجز',

            self::PATIENT_DAY_APPOINTMENT_REMINDER => 'تذكير بموعد',

            self::PATIENT_FEEDBACK => 'رسالة جديدة',

            self::DOCTOR_DAY_APPOINTMENTS => 'مواعيد اليوم',

            self::PATIENT_CONSULTATION_REMINDER_WEEK,
            self::PATIENT_CONSULTATION_REMINDER_DAY => 'تذكير بموعد الاستشارة',
        };
    }

    public function body(array $data = []): string
    {
        // Accepts a string or a Carbon instance; strings are converted to Carbon.
        $date = fn (string $key): string => ArabicDateFormatter::format(
            $data[$key] instanceof Carbon ? $data[$key] : Carbon::parse($data[$key])
        );

        // Time only, for same-day notifications where repeating the date is noise.
        $time = fn (string $key): string => ArabicDateFormatter::formatTime(
            ($data[$key] instanceof Carbon ? $data[$key] : Carbon::parse($data[$key]))->format('H:i')
        );

        return match ($this) {
            self::PATIENT_APPOINTMENT_BOOKED => "لديك موعد جديد {$data['patient_name']} ".$date('date'),

            self::PATIENT_APPOINTMENT_RESCHEDULED => "{$data['patient_name']} قام بتعديل موعده من "
                .$date('from_date').' ليصبح '.$date('to_date'),

            self::PATIENT_APPOINTMENT_CANCEL => "{$data['patient_name']} قام بإلغاء موعده ".$date('date'),

            self::DOCTOR_APPOINTMENT_BOOKED => 'تم حجز موعدك في '.$date('date'),

            self::DOCTOR_APPOINTMENT_RESCHEDULED => 'تم تعديل موعدك من '.$date('from_date').' ليصبح '.$date('to_date'),

            self::DOCTOR_APPOINTMENT_CANCEL => 'تم إلغاء موعدك في '.$date('date'),

            self::PATIENT_DAY_APPOINTMENT_REMINDER => 'لديك موعد الساعة '.$time('date'),

            self::PATIENT_FEEDBACK => "{$data['patient']} أرسل رسالة\n{$data['message']}\n{$data['phone']}",

            self::DOCTOR_DAY_APPOINTMENTS => "مرحبا {$data['name']}\nلديك اليوم ".ArabicDateFormatter::toArabicDigits($data['confirmed'])
                .' مواعيد مؤكدة و '.ArabicDateFormatter::toArabicDigits($data['available'])
                ." متاحين للحجز حتى الآن\nأول موعد اليوم الساعة {$data['time']}",

            self::PATIENT_CONSULTATION_REMINDER_WEEK,
            self::PATIENT_CONSULTATION_REMINDER_DAY => 'موعد الاستشارة '.ArabicDateFormatter::format(
                $data['date'] instanceof Carbon ? $data['date'] : Carbon::parse($data['date']),
                includeTime: false
            ),
        };
    }

    /**
     * The conversation state a stateful WhatsApp notification puts the
     * patient's conversation in, so their button reply is routed back to it.
     */
    public function state(): ?ConversationState
    {
        return match ($this) {
            self::PATIENT_CONSULTATION_REMINDER_WEEK,
            self::PATIENT_CONSULTATION_REMINDER_DAY => ConversationState::CONSULTATION_REMINDER,

            default => null,
        };
    }

    public function link(array $data = []): ?string
    {
        return match ($this) {
            default => '/',
        };
    }
}
