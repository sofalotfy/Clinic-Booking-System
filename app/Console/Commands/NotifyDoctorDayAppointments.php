<?php

namespace App\Console\Commands;

use App\Enums\NotificationEnum;
use App\Models\Day;
use App\Services\Notifications\NotificationManager;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Throwable;

class NotifyDoctorDayAppointments extends Command
{
    protected $signature = 'app:notify-doctor-day-appointments
                            {--at= : Explicit date (Y-m-d) to run for instead of today}';

    protected $description = 'Notify each doctor of their appointments for the day.';

    public function handle(): int
    {
        try {
            $target = $this->option('at')
                ? Carbon::createFromFormat('Y-m-d', $this->option('at'))->startOfDay()
                : today();
        } catch (Throwable $exception) {
            $this->error("Invalid --at value [{$this->option('at')}], expected Y-m-d.");

            return self::FAILURE;
        }

        $days = Day::query()
            ->with(['doctor.user', 'doctor.clinic'])
            ->active()
            ->whereHas('doctor', fn ($query) => $query->notSuspended())
            ->whereDate('date', $target->toDateString())
            ->get();

        if ($days->isEmpty()) {
            $this->info("No schedules for {$target->toDateString()}.");

            return self::SUCCESS;
        }

        $sent = 0;
        $skipped = 0;
        $failed = 0;

        foreach ($days as $day) {
            $sender = $day->doctor?->user;

            if (! $sender) {
                $skipped++;

                continue;
            }

            try {
                NotificationManager::execute(
                    $sender,
                    $day->doctor_id,
                    NotificationEnum::DOCTOR_DAY_APPOINTMENTS,
                    $day
                );

                $sent++;
            } catch (Throwable $exception) {
                \Log::error(
                    "Day appointments notification for day {$day->id} failed: ".$exception->getMessage()
                );

                $failed++;
            }
        }

        $this->info(
            "Day appointments for {$target->toDateString()}: {$sent} sent, {$skipped} skipped, {$failed} failed."
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
