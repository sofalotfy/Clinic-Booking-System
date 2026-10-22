<?php

namespace App\Services\Costs;

use App\Models\Cost;

class DeleteCost
{
    public static function execute(Cost $cost)
    {
        return $cost->delete();
    }
}
