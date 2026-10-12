<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Checks\CheckDayOccupied;
use App\Services\DaysInstances\Modifications\TransferDay as TransferService;
use Illuminate\Validation\ValidationException;

class TransferDay
{
    public static function execute($request, $day)
    {
        $validated = $request->validate([
            'new_date' => ['required', 'date'],
        ]);

        if (CheckDayOccupied::execute($day->doctor_id, $validated['new_date'])) {
            throw ValidationException::withMessages([
                'new_date' => 'The chosen date is already occupied.',
            ]);
        }

        return TransferService::execute($request->user(), $day, $validated['new_date']);
    }
}
