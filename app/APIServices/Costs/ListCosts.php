<?php

namespace App\APIServices\Costs;

use App\Services\Costs\ListCosts as ListService;

class ListCosts
{
    public static function execute($request)
    {
        $filters = $request->validate([
            'search' => 'nullable|string|max:255',
            'cost_category_id' => 'nullable|integer|exists:cost_categories,id',
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
            'per_page' => 'nullable|integer|min:1|max:100',
        ]);

        return ListService::execute($request->user(), $filters)
            ->with('category:id,name')
            ->paginate($filters['per_page'] ?? 15)
            ->through(fn ($cost) => ShowCost::execute($cost));
    }
}
