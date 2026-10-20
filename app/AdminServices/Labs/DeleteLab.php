<?php

namespace App\AdminServices\Labs;

use App\Models\Lab;
use App\Services\Labs\DeleteLab as DeleteLabService;

class DeleteLab
{
    public static function execute(Lab $lab)
    {
        return DeleteLabService::execute($lab);
    }
}
