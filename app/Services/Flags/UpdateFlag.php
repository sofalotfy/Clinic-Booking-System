<?php

namespace App\Services\Flags;

use App\Models\Flag;

class UpdateFlag
{
    public static function execute($flag, $data)
    {
        return $flag->update([
            'name' => $data['name'] ?? $flag->name,
            'color' => $data['color'] ?? $flag->color,
            'description' => $data['description'] ?? $flag->description,
        ]);
    }
}