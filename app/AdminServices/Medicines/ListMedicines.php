<?php

namespace App\AdminServices\Medicines;

use App\Services\Medicines\ListMedicines as ListMedicinesService;

class ListMedicines
{
    public static function execute($request)
    {
        return ListMedicinesService::execute($request->only(['search']))
            ->paginate(25)
            ->withQueryString()
            ->through(fn ($medicine) => ShowMedicine::execute($medicine));
    }
}
