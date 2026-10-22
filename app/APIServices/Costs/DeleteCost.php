<?php

namespace App\APIServices\Costs;

use App\Models\Cost;
use App\Services\Costs\DeleteCost as DeleteService;

class DeleteCost
{
    public static function execute(Cost $cost): void
    {
        DeleteService::execute($cost);
    }
}
