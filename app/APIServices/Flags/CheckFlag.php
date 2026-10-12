<?php

namespace App\APIServices\Flags;

use App\Models\Flag;

class CheckFlag
{
    public static function execute(Flag $flag): array
    {
        return [
            'effected' => $flag->patients()->count(),
        ];
    }
}
