<?php

namespace App\Services\Medicines;

use App\Models\Medicine;

class ListMedicines
{
    public static function execute(array $filters = [])
    {
        return Medicine::query()
            ->orderBy('name')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['active_ingredient']), fn ($query) => $query->where('active_ingredient', $filters['active_ingredient']))
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->when(isset($filters['dosage_form']), fn ($query) => $query->where('dosage_form', $filters['dosage_form']))
            ->when(isset($filters['is_active']), fn ($query) => $query->where('is_active', (bool) $filters['is_active']));
    }
}
