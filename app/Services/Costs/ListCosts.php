<?php

namespace App\Services\Costs;

use App\Models\Cost;

class ListCosts
{
    public static function execute($user, array $filters = [])
    {
        return Cost::query()
            ->where('costs.doctor_id', $user->clinicDoctorId())
            ->when($filters['search'] ?? null, fn ($query, $term) => $query->where('costs.name', 'like', "%{$term}%"))
            ->when($filters['cost_category_id'] ?? null, fn ($query, $id) => $query->where('costs.cost_category_id', $id))
            ->when($filters['from'] ?? null, fn ($query, $date) => $query->whereDate('costs.date', '>=', $date))
            ->when($filters['to'] ?? null, fn ($query, $date) => $query->whereDate('costs.date', '<=', $date))
            ->orderByDesc('costs.date')
            ->orderByDesc('costs.id');
    }
}
