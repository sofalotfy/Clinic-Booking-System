<?php

namespace App\Services\Costs;

use App\Models\Cost;

class UpdateCost
{
    public static function execute(Cost $cost, array $data): Cost
    {
        $cost->update($data);

        return $cost->refresh();
    }
}
