<?php

namespace App\Services\IdempotencyKeys;

use App\Models\IdempotencyKey;

class LogIdempotencyKey
{
    public static function execute($key): bool
    {
        if (blank($key)) {
            return false;
        }

        if (IdempotencyKey::where('key', $key)->exists()) {
            return false;
        }

        IdempotencyKey::create(['key' => $key]);

        return true;
    }
}