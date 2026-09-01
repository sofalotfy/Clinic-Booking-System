<?php

namespace App\APIServices\Assistants;

use App\Services\Assistants\DeleteAssistant as DeleteService;

class DeleteAssistant
{
    public static function execute($assistant)
    {
        return DeleteService::execute($assistant);
    }
}