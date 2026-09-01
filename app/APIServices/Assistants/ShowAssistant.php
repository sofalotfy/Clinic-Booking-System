<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;
use App\Services\Assistants\ShowAssistant as ShowAssistantService;

class ShowAssistant
{
    public static function execute(Assistant $assistant)
    {
        $assistant = ShowAssistantService::execute($assistant);

        return $assistant;
    }
}