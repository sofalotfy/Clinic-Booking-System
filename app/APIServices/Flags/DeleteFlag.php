<?php

namespace App\APIServices\Flags;

use App\Services\Flags\DeleteFlag as DeleteService;

class DeleteFlag
{
    public static function execute($flag): void
    {
        DeleteService::execute($flag);
    }
}