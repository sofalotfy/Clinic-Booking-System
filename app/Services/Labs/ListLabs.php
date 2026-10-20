<?php

namespace App\Services\Labs;

use App\Models\Lab;

class ListLabs
{
    public static function execute(array $filters = [])
    {
        return Lab::query()
            ->orderBy('name')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['category']), fn ($query) => $query->where('category', $filters['category']))
            ->when(isset($filters['sample_type']), fn ($query) => $query->where('sample_type', $filters['sample_type']))
            ->when(isset($filters['is_active']), fn ($query) => $query->where('is_active', (bool) $filters['is_active']));
    }
}
