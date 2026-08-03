<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;
use App\Enums\UserType;

class DeleteAssistant
{
    public static function execute($assistant)
    {
        $assistant->user->delete();
    }
}