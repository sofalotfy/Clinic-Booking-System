<?php

namespace App\Console\Commands;

use App\Models\IdempotencyKey;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PruneIdempotencyKeys extends Command
{
    protected $signature = 'app:prune-idempotency-keys';

    protected $description = 'Delete idempotency keys older than 10 days.';

    public function handle(): int
    {
        $deleted = IdempotencyKey::where(
            'created_at', '<', now()->subDays(10)
        )->delete();

        Log::info('Pruned old idempotency keys', ['deleted' => $deleted]);

        return self::SUCCESS;
    }
}