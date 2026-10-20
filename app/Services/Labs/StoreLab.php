<?php

namespace App\Services\Labs;

use App\Models\Lab;

class StoreLab
{
    public static function execute(array $data): Lab
    {
        return Lab::create($data);
    }
}
