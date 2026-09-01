<?php

namespace App\APIServices\Flags;

use App\Services\Flags\ListFlags as ListService;

class ListFlags
{
    public static function execute($request)
    {
        $patients = ListService::execute($request->user())
                ->select(self::getSelects())
                ->get();

        return response()->json([
            'patinets' => $patients,
        ]);
    }

    private static function getSelects(): array
    {
        return [
            'flags.id',
            'flags.name',
            'flags.color',
            'flags.description',
        ];
    }
}