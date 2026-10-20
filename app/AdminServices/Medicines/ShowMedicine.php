<?php

namespace App\AdminServices\Medicines;

use App\Models\Medicine;

class ShowMedicine
{
    public static function execute(Medicine $medicine): array
    {
        return [
            'id' => $medicine->id,
            'name' => $medicine->name,
            'name_ar' => $medicine->name_ar,
            'active_ingredient' => $medicine->active_ingredient,
            'price' => $medicine->price,
            'major_units' => $medicine->major_units,
            'unit_price' => $medicine->unit_price,
            'minor_units' => $medicine->minor_units,
            'category' => $medicine->category,
            'company' => $medicine->company,
            'size' => $medicine->size,
            'strength' => $medicine->strength,
            'barcode' => $medicine->barcode,
            'dosage_form' => $medicine->dosage_form,
            'origin' => $medicine->origin,
            'image_url' => $medicine->image_url,
            'is_active' => $medicine->is_active,
            'created_at' => $medicine->created_at?->format('Y-m-d'),
            'updated_at' => $medicine->updated_at?->format('Y-m-d H:i'),
        ];
    }
}
