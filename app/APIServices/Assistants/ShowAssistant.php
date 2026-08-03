<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;

class ShowAssistant
{
    public static function execute(Assistant $assistant)
    {
        $assistant->load([
            'user.roles:id,name',
            'user:id,name,email,phone'
        ]);

        return $assistant;
    }
}