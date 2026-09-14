<?php

namespace App\Console\Commands;

use App\Enums\TemplatePlanStatus;
use App\Models\TemplatePlan;
use App\Services\DaysInstances\Creations\StoreDay;
use Illuminate\Console\Command;

class GenerateDoctorSchedules extends Command
{
    protected $signature = 'app:generate-doctor-schedules';

    protected $description = 'Generate the next 30 days for all active doctor plans.';

    public function handle(): int
    {
        $plans = TemplatePlan::with('templateDays')
            ->where('status', TemplatePlanStatus::ACTIVE)
            ->get();

        foreach ($plans as $plan) {
            StoreDay::execute(
                null,
                $plan,
                now()->addDays(30)->toDateString()
            );
        }

        return self::SUCCESS;
    }
}