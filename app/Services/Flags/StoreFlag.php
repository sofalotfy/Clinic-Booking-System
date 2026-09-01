<?php

namespace App\Services\Flags;

use App\Models\Flag;

class StoreFlag
{
    public static function execute($user, $data)
    {
        return Flag::create([
            'doctor_id' => $user->clinicDoctor()->id,
            'name' => $data['name'],
            'color' => $data['color'],
            'description' => $data['description'] ?? null,
        ]);
    }
}