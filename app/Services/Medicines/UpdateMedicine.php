<?php

namespace App\Services\Medicines;

use App\Models\Medicine;

class UpdateMedicine
{
    public static function execute(Medicine $medicine, array $data): Medicine
    {
        $medicine->update($data);

        return $medicine->refresh();
    }
}
