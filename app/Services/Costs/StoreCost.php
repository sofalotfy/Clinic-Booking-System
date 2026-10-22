<?php

namespace App\Services\Costs;

use App\Models\Cost;

class StoreCost
{
    public static function execute($user, array $data): Cost
    {
        return Cost::create([
            ...$data,
            'doctor_id' => $user->clinicDoctor()->id,
        ]);
    }
}
