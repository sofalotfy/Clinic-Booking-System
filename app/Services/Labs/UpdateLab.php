<?php

namespace App\Services\Labs;

use App\Models\Lab;

class UpdateLab
{
    public static function execute(Lab $lab, array $data): Lab
    {
        $lab->update($data);

        return $lab->refresh();
    }
}
