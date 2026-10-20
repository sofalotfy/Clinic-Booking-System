<?php

namespace App\AdminServices\Medicines;

use App\Models\Medicine;
use App\Services\Medicines\UpdateMedicine as UpdateMedicineService;

class UpdateMedicine
{
    public static function execute($request, Medicine $medicine)
    {
        return UpdateMedicineService::execute($medicine, $request->validate(StoreMedicine::rules($medicine)));
    }
}
