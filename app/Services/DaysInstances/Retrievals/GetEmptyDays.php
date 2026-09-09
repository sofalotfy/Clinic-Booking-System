<?php

namespace App\Services\DaysInstances\Retrievals;

use App\Models\Day;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;

class GetEmptyDays
{
    public static function execute(User $user, $clinicId): array
    {
        $startDate = Carbon::today();
        $endDate = Carbon::today()->addDays(30);

        $existingDates = Day::where('doctor_id', $user->clinicDoctorId())
            ->where('doctor_id', $clinicId)
            ->whereBetween('date', [$startDate->toDateString(), $endDate->toDateString()])
            ->pluck('date')
            ->map(fn ($date) => Carbon::parse($date)->toDateString())
            ->toArray();

        $allDates = collect(CarbonPeriod::create($startDate, $endDate))
            ->map(fn ($date) => $date->toDateString())
            ->toArray();

        $emptyDates = array_values(array_diff($allDates, $existingDates));

        return $emptyDates;
    }
}