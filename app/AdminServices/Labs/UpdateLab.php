<?php

namespace App\AdminServices\Labs;

use App\Models\Lab;
use App\Services\Labs\UpdateLab as UpdateLabService;

class UpdateLab
{
    public static function execute($request, Lab $lab)
    {
        return UpdateLabService::execute($lab, $request->validate(StoreLab::rules($lab)));
    }
}
