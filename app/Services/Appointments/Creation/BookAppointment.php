<?php

namespace App\Services\Appointments\Creation;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationEnum;
use App\Models\Appointment;
use App\Models\Day;
use App\Services\Appointments\Modifications\UnConfirmAppointment;
use App\Services\Notifications\NotificationManager;
use App\Services\Patients\Checks\CheckBookingLimitExceeded;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotExistance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookAppointment
{
    /**
     * Authoritative booking entry point. Every caller (API, admin, console)
     * gets the same checks, run under a row lock on the day so two requests
     * cannot book the same slot.
     */
    public static function execute($user, $patient, $day, $time, $status = AppointmentStatus::ACTIVE)
    {
        if (! $day) {
            throw ValidationException::withMessages([
                'error' => 'The day is not available.',
            ]);
        }

        return DB::transaction(function () use ($user, $patient, $day, $time, $status) {
            // Serialize all bookings for this day and re-read it under the lock.
            $day = Day::active()->whereKey($day->id)->lockForUpdate()->first();

            if (! $day) {
                throw ValidationException::withMessages([
                    'error' => 'The day is not available.',
                ]);
            }

            // Build a clean datetime even if Day::date is cast to a date/datetime.
            $date = Carbon::parse($day->date)->format('Y-m-d').' '.$time;

            if ($status === AppointmentStatus::ACTIVE && Carbon::parse($date)->isPast()) {
                throw ValidationException::withMessages([
                    'error' => 'This time has already passed.',
                ]);
            }

            if (CheckBookingLimitExceeded::execute($day->doctor_id, $patient->id)) {
                throw ValidationException::withMessages([
                    'error' => 'You have reached the maximum number of appointments.',
                ]);
            }

            if ($status === AppointmentStatus::ACTIVE) {
                if (! CheckSlotExistance::execute($day, $time)) {
                    throw ValidationException::withMessages([
                        'error' => 'This slot is not available.',
                    ]);
                }

                if (! CheckSlotAvailability::execute($day, $time)) {
                    throw ValidationException::withMessages([
                        'error' => 'This slot is already booked.',
                    ]);
                }
            } elseif (! CheckAvailability::execute($day)) {
                throw ValidationException::withMessages([
                    'error' => 'The Day is fully booked.',
                ]);
            }

            // BOOK APPOINTMENT
            $appointment = Appointment::create([
                'doctor_id' => $day->doctor_id,
                'patient_id' => $patient->id,
                'date' => $date,
                'duration' => $day->appointment_duration,
                'status' => $status,
            ]);

            $notification = NotificationEnum::PATIENT_APPOINTMENT_BOOKED;

            if (! $user->isPatient()) {
                $notification = NotificationEnum::DOCTOR_APPOINTMENT_BOOKED;
                UnConfirmAppointment::execute($user, $appointment);
            }

            // Fire only after the outermost transaction commits, so a notification
            // failure cannot roll back a valid booking and queued jobs always see the row.
            DB::afterCommit(fn () => NotificationManager::execute(
                $user,
                $appointment->doctor_id,
                $notification,
                $appointment
            ));

            return $appointment;
        });
    }
}
