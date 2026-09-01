<?php

namespace App\Services\Assistants;

use App\Models\Assistant;

class ShowAssistant
{
    public static function execute(Assistant $assistant)
    {
        return $assistant->load([
            'user.roles:id,name',
            'user:id,name,email,phone'
        ]);
    }
}