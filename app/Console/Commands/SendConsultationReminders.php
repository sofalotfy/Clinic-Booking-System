<?php

namespace App\Console\Commands;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationEnum;
use App\Models\Appointment;
use App\Models\Prescription;
use App\Services\Notifications\NotificationManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class SendConsultationReminders extends Command
{
    protected $signature = 'app:send-consultation-reminders
                            {--at= : Run as if today were this date (Y-m-d)}';

    protected $description = 'Remind patients of their prescription consultation one week and one day before it.';

    /**
     * Days before the consultation => [notification, column stamped once sent]
     */
    private const REMINDERS = [
        7 => [NotificationEnum::PATIENT_CONSULTATION_REMINDER_WEEK, 'week_reminder_sent_at'],
        1 => [NotificationEnum::PATIENT_CONSULTATION_REMINDER_DAY, 'day_reminder_sent_at'],
    ];

    public function handle(): int
    {
        try {
            $today = $this->option('at')
                ? Carbon::createFromFormat('Y-m-d', $this->option('at'))->startOfDay()
                : today();
        } catch (Throwable $exception) {
            $this->error("Invalid --at value [{$this->option('at')}], expected Y-m-d.");

            return self::FAILURE;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach (self::REMINDERS as $daysBefore => [$notification, $sentColumn]) {
            $prescriptions = Prescription::query()
                ->with(['doctor.user', 'patient.user', 'appointment'])
                ->whereDate('consultation_date', $today->copy()->addDays($daysBefore)->toDateString())
                ->whereNull($sentColumn)
                ->whereHas('doctor', fn ($query) => $query->notSuspended())
                ->get();

            foreach ($prescriptions as $prescription) {
                if (! $this->shouldRemind($prescription)) {
                    $skipped++;

                    continue;
                }

                try {
                    NotificationManager::execute(
                        $prescription->doctor->user,
                        $prescription->doctor_id,
                        $notification,
                        $prescription
                    );

                    $prescription->update([$sentColumn => now()]);
                    $sent++;
                } catch (Throwable $exception) {
                    \Log::error("Consultation reminder for prescription {$prescription->id} failed: ".$exception->getMessage());

                    $failed++;
                }
            }
        }

        $this->info("Consultation reminders for {$today->toDateString()}: {$sent} sent, {$skipped} skipped, {$failed} failed.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }

    /**
     * No reminder for a patient the clinic has blocked, or one who has booked
     * the doctor again since the prescription: any appointment after the
     * prescription's own one that is not cancelled, upcoming or already
     * attended. The prescription's own appointment never counts.
     */
    private function shouldRemind(Prescription $prescription): bool
    {
        if (! $prescription->doctor?->user || ! $prescription->patient?->user) {
            return false;
        }

        if ($prescription->patient->isBlocked($prescription->doctor)) {
            return false;
        }

        return ! Appointment::query()
            ->where('doctor_id', $prescription->doctor_id)
            ->where('patient_id', $prescription->patient_id)
            ->whereKeyNot($prescription->appointment_id)
            ->where('status', '!=', AppointmentStatus::CANCELLED)
            ->where('date', '>', $prescription->appointment->date)
            ->exists();
    }
}
