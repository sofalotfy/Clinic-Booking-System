<?php

namespace App\Services\Medicines;

use App\Models\Medicine;

class StoreMedicine
{
    public static function execute(array $data): Medicine
    {
        return Medicine::create($data);
    }
}
