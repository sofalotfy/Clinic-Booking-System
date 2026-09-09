<?php

namespace App\APIServices\Days;

use App\Services\DaysInstances\Modifications\TransferDay as TransferService;

class TransferDay
{
    public static function execute($request, $day)
    {
        return TransferService::execute($request->user(), $day, $request->new_date);
    }
}   