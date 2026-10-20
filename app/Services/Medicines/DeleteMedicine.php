<?php

namespace App\Services\Medicines;

use App\Models\Medicine;

class DeleteMedicine
{
    /**
     * Soft deletes, so prescriptions that already reference the medicine keep
     * it. Later sheet uploads still refresh its data but do not restore it.
     */
    public static function execute(Medicine $medicine)
    {
        return $medicine->delete();
    }
}
