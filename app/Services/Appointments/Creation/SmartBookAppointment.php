<?php

namespace App\Services\Appointments\Creation;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationEnum;
use App\Models\Appointment;
use App\Models\Day;
use App\Models\Patient;
use App\Services\Notifications\NotificationManager;
use App\Services\Patients\Checks\CheckBookingLimitExceeded;
use App\Services\TemplatePlans\Checks\CheckAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotAvailability;
use App\Services\TemplatePlans\Checks\CheckSlotExistance;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class SmartBookAppointment
{
    // ASSUMPTION
    //   A patient can only have one working appointment per doctor.
    //   Booking again reschedules the existing one.
    //
    // USED IN WHATSAPP TOOL
    //   Throws ValidationException when the booking is not possible, so the
    //   caller must catch it and turn the message into a reply.

    public static function execute($patient, $doctor, $date, $duration, $status)
    {
        $dateTime = Carbon::parse($date);
        $time = $dateTime->format('H:i');

        [$appointment, $notificationType, $oldDate] = DB::transaction(
            function () use ($patient, $doctor, $dateTime, $time, $duration, $status) {
                // LOCK ORDER: patient first, then day. Always the same order to avoid deadlocks.
                // Locking the patient stops two simultaneous messages from the same
                // person creating two appointments.
                Patient::whereKey($patient->id)->lockForUpdate()->first();

                // Locking the day serializes every booking for that day, so two
                // patients cannot take the same slot.
                $day = Day::where('doctor_id', $doctor->id)
                    ->whereDate('date', $dateTime->toDateString())
                    ->active()
                    ->lockForUpdate()
                    ->first();

                if (! $day) {
                    throw ValidationException::withMessages([
                        'error' => 'The day is not available.',
                    ]);
                }

                if ($status === AppointmentStatus::ACTIVE && $dateTime->isPast()) {
                    throw ValidationException::withMessages([
                        'error' => 'This time has already passed.',
                    ]);
                }

                // FETCH CURRENT APPOINTMENT (under the patient lock)
                $appointment = Appointment::where('doctor_id', $doctor->id)
                    ->where('patient_id', $patient->id)
                    ->whereIn('status', AppointmentStatus::working())
                    ->lockForUpdate()
                    ->first();

                // If the patient asks for the slot they already hold, their own
                // appointment must not count against availability.
                $sameSlot = $appointment
                    && Carbon::parse($appointment->date)->format('Y-m-d H:i') === $dateTime->format('Y-m-d H:i');

                $sameDay = $appointment
                    && Carbon::parse($appointment->date)->isSameDay($dateTime);

                // A new booking counts toward the limit; a reschedule does not add one.
                if (! $appointment && CheckBookingLimitExceeded::execute($day->doctor_id, $patient->id)) {
                    throw ValidationException::withMessages([
                        'error' => 'You have reached the maximum number of appointments.',
                    ]);
                }

                if ($status === AppointmentStatus::ACTIVE) {
                    if (! $sameSlot) {
                        \Log::info("onto checks");
                        if (! CheckSlotExistance::execute($day, $time)) {
                            throw ValidationException::withMessages([
                                'error' => 'This slot is not available.',
                            ]);
                        }

                        if (! CheckSlotAvailability::execute($day, $time)) {
                            \Log::info("checking slot availability");
                            throw ValidationException::withMessages([
                                'error' => 'This slot is already booked.',
                            ]);
                        }
                    }
                } elseif (! $sameDay && ! CheckAvailability::execute($day)) {
                    throw ValidationException::withMessages([
                        'error' => 'The Day is fully booked.',
                    ]);
                }

                $oldDate = null;

                if ($appointment) {
                    $oldDate = $appointment->date;

                    $appointment->update([
                        'date' => $dateTime->format('Y-m-d H:i:s'),
                        'duration' => $duration,
                        'status' => $status,
                    ]);

                    $notificationType = NotificationEnum::PATIENT_APPOINTMENT_RESCHEDULED;
                } else {
                    $appointment = Appointment::create([
                        'doctor_id' => $doctor->id,
                        'patient_id' => $patient->id,
                        'date' => $dateTime->format('Y-m-d H:i:s'),
                        'duration' => $duration,
                        'status' => $status,
                    ]);

                    $notificationType = NotificationEnum::PATIENT_APPOINTMENT_BOOKED;
                }

                return [$appointment->fresh(), $notificationType, $oldDate];
            }
        );

        if ($oldDate) {
            $appointment->old_date = $oldDate;
        }

        // Sent after the transaction has committed. A notification failure
        // can no longer undo a booking that already succeeded.
        NotificationManager::execute($patient->user, $doctor->id, $notificationType, $appointment);

        return $appointment;
    }
}
