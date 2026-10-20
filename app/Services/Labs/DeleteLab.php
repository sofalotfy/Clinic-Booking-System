<?php

namespace App\Services\Labs;

use App\Models\Lab;

class DeleteLab
{
    /**
     * Soft deletes, so prescriptions that already reference the lab keep it.
     */
    public static function execute(Lab $lab)
    {
        return $lab->delete();
    }
}
