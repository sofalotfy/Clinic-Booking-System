<?php

namespace App\APIServices\Flags;

use App\Models\Flag;
use Illuminate\Validation\ValidationException;

class DeleteFlag
{
    public static function execute(int $flag_id): void
    {
        $flag = Flag::where('id', $flag_id)
            ->where('doctor_id', auth()->user()->doctor->id)
            ->first();

        if (! $flag) {
            throw ValidationException::withMessages([
                'flag_id' => ['The selected flag is invalid.'],
            ]);
        }

        // Remove all patient relationships
        $flag->patients()->detach();

        // Delete the flag
        $flag->delete();
    }
}