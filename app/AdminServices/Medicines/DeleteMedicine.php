<?php

namespace App\AdminServices\Medicines;

use App\Models\Medicine;
use App\Services\Medicines\DeleteMedicine as DeleteMedicineService;

class DeleteMedicine
{
    public static function execute(Medicine $medicine)
    {
        return DeleteMedicineService::execute($medicine);
    }
}
