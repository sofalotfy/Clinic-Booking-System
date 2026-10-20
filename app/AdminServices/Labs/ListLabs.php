<?php

namespace App\AdminServices\Labs;

use App\Services\Labs\ListLabs as ListLabsService;

class ListLabs
{
    public static function execute($request)
    {
        return ListLabsService::execute($request->only(['search']))
            ->paginate(15)
            ->withQueryString()
            ->through(fn ($lab) => ShowLab::execute($lab));
    }
}
