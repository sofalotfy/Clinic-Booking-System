<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Enums\TemplatePlanStatus;
use App\Services\TemplatePlans\GeneratePlanDays;
use App\Models\TemplatePlan;

class GenerateDoctorSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-doctor-schedules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate the next 30 days for all active doctor plans.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $plans = TemplatePlan::with('templateDays')
            ->where('status', TemplatePlanStatus::ACTIVE)
            ->get();

        foreach ($plans as $plan) {
            GeneratePlanDays::execute($plan);
        }

        return self::SUCCESS;
    }
}
