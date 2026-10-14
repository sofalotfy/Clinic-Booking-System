<?php

namespace App\Services\Doctors;

use App\Models\Doctor;
use App\Models\Suspension;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\PersonalAccessToken;

class SuspendDoctor
{
    public static function execute(Doctor $doctor, ?User $actor = null, ?string $reason = null): Suspension
    {
        if ($active = $doctor->activeSuspension) {
            return $active;
        }

        return DB::transaction(function () use ($doctor, $actor, $reason) {
            $suspension = $doctor->suspensions()->create([
                'suspended_by' => $actor?->id,
                'reason' => $reason,
                'suspended_at' => now(),
            ]);

            // Sign the doctor and the clinic assistants out of the app.
            $userIds = $doctor->assistants()->pluck('user_id')->push($doctor->user_id);

            PersonalAccessToken::query()
                ->where('tokenable_type', (new User)->getMorphClass())
                ->whereIn('tokenable_id', $userIds)
                ->delete();

            $doctor->unsetRelation('activeSuspension');

            return $suspension;
        });
    }
}
