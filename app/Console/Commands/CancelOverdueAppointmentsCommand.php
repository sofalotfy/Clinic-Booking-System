<?php

namespace App\Console\Commands;

use App\Services\Appointments\Modifications\CancelOverdueAppointments;
use Illuminate\Console\Command;

class CancelOverdueAppointmentsCommand extends Command
{
    protected $signature = 'app:cancel-overdue-appointments';

    protected $description = 'Cancel any working appointment whose date has already passed.';

    public function handle(): int
    {
        $cancelled = CancelOverdueAppointments::execute();

        $this->info("Cancelled {$cancelled} overdue appointment(s).");

        return self::SUCCESS;
    }
}
