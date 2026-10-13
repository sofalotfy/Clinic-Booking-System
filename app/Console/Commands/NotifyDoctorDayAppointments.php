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
                            {--lead=2 : Hours before the schedule starts to notify}
                            {--at= : Explicit target datetime (Y-m-d H:i) used instead of now + lead}';

    protected $description = 'Notify each doctor of their day appointments two hours before the schedule starts.';

    public function handle(): int
    {
        try {
            $target = $this->option('at')
                ? Carbon::parse($this->option('at'))
                : now()->addHours((int) $this->option('lead'));
        } catch (Throwable $exception) {
            $this->error("Invalid --at value [{$this->option('at')}], expected Y-m-d H:i.");

            return self::FAILURE;
        }

        $days = Day::query()
            ->with(['doctor.user', 'doctor.clinic'])
            ->active()
            ->whereDate('date', $target->toDateString())
            ->whereBetween('start_time', [
                $target->format('H:i:00'),
                $target->format('H:i:59'),
            ])
            ->get();

        if ($days->isEmpty()) {
            $this->info("No schedules starting at {$target->format('Y-m-d H:i')}.");

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
            "Day appointments for {$target->format('Y-m-d H:i')}: {$sent} sent, {$skipped} skipped, {$failed} failed."
        );

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
