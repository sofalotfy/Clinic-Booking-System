<?php

namespace App\APIServices\Costs;

use App\Models\Cost;

class ShowCost
{
    public static function execute(Cost $cost): array
    {
        $cost->loadMissing('category:id,name');

        return [
            'id' => $cost->id,
            'name' => $cost->name,
            'cost_category_id' => $cost->cost_category_id,
            'category' => $cost->category?->name,
            'amount' => $cost->amount,
            'date' => $cost->date?->format('Y-m-d'),
            'notes' => $cost->notes,
        ];
    }
}
