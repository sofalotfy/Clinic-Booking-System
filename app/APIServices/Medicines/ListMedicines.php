<?php

namespace App\APIServices\Medicines;

use App\Services\Medicines\ListMedicines as ListService;

class ListMedicines
{
    public static function execute($request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'active_ingredient' => 'nullable|string|max:255',
            'category' => 'nullable|string|max:255',
            'dosage_form' => 'nullable|string|max:255',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        // Clinics only ever pick from the active catalog
        return ListService::execute([...$filters, 'is_active' => true])
            ->select(self::getSelects())
            ->paginate($filters['per_page'] ?? 50);
    }

    public static function getSelects(): array
    {
        return [
            'medicines.id',
            'medicines.name',
            'medicines.name_ar',
            'medicines.active_ingredient',
            'medicines.price',
            'medicines.major_units',
            'medicines.unit_price',
            'medicines.minor_units',
            'medicines.category',
            'medicines.company',
            'medicines.size',
            'medicines.strength',
            'medicines.barcode',
            'medicines.dosage_form',
            'medicines.origin',
            'medicines.image_url',
        ];
    }
}
