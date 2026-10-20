<?php

namespace App\Services\Scans;

use App\Models\Scan;

class ListScans
{
    public static function execute(array $filters = [])
    {
        return Scan::query()
            ->orderBy('name')
            ->search($filters['search'] ?? null)
            ->when(isset($filters['body_part']), fn ($query) => $query->where('body_part', $filters['body_part']))
            ->when(isset($filters['modality']), fn ($query) => $query->where('modality', $filters['modality']))
            ->when(isset($filters['is_active']), fn ($query) => $query->where('is_active', (bool) $filters['is_active']));
    }
}
