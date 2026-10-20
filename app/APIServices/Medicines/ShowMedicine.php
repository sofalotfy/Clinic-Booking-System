<?php

namespace App\APIServices\Medicines;

use App\Models\Medicine;

class ShowMedicine
{
    public static function execute(Medicine $medicine)
    {
        return $medicine->only([
            'id',
            'name',
            'name_ar',
            'active_ingredient',
            'price',
            'major_units',
            'unit_price',
            'minor_units',
            'category',
            'company',
            'size',
            'strength',
            'barcode',
            'dosage_form',
            'origin',
            'image_url',
            'is_active',
        ]);
    }
}
