<?php

namespace App\Services\Flags;

use App\Models\Flag;

class DeleteFlag
{
    public static function execute($flag)
    {
        $flag->patients()->detach();
        
        return $flag->delete();
    }
}