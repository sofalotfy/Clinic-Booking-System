<?php

namespace App\APIServices\Assistants;

use App\Services\Assistants\ListAssistants as ListAssistantsService;

class ListAssistants
{
    public static function execute($user)
    {
        return ListAssistantsService::execute($user)->get();
    }
}