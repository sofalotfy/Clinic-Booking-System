<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestScheduler extends Command
{
    protected $signature = 'app:test-scheduler';

    protected $description = 'Test Laravel scheduler';

    public function handle()
    {
        Log::info('Laravel scheduler is working!', [
            'time' => now()->toDateTimeString(),
        ]);

        return self::SUCCESS;
    }
}