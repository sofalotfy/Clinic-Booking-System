<?php

namespace App\Console\Commands;

use App\Enums\NotificationEnum;
use App\Models\Appointment;
use App\Services\Notifications\NotificationManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class SendAppointmentReminders extends Command
{
    protected $signature = 'app:send-appointment-reminders
                            {date? : The day to remind for, defaults to today (Y-m-d)}';

    protected $description = 'Send a stateless WhatsApp reminder to each patient with an appointment on the given day.';

    public function handle(): int
    {
        try {
            $date = $this->argument('date')
                ? Carbon::parse($this->argument('date'))->startOfDay()
                : Carbon::today();
        } catch (Throwable $exception) {
            $this->error("Invalid date [{$this->argument('date')}], expected Y-m-d.");

            return self::FAILURE;
        }

        $appointments = Appointment::query()
            ->with(['patient.user', 'doctor.user'])
            ->active()
            ->whereDate('date', $date->toDateString())
            ->get();

        if ($appointments->isEmpty()) {
            $this->info("No appointments to remind for {$date->toDateString()}.");

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            $sender = $appointment->doctor?->user;

            if (! $sender) {
                $skipped++;

                continue;
            }

            try {
                NotificationManager::execute(
                    $sender,
                    $appointment->doctor_id,
                    NotificationEnum::PATIENT_DAY_APPOINTMENT_REMINDER,
                    $appointment
                );

                $sent++;
            } catch (Throwable $exception) {
                \Log::error(
                    "Reminder for appointment {$appointment->id} failed: ".$exception->getMessage()
                );

                $failed++;
            }
        }

        $this->info(
            "Reminders for {$date->toDateString()}: {$sent} sent, {$skipped} skipped, {$failed} failed."
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
