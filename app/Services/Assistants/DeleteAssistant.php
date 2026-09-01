<?php

namespace App\Services\Assistants;

use App\Models\Assistant;

class DeleteAssistant
{
    public static function execute(Assistant $assistant)
    {
        $assistant->user()->delete();
        return $assistant->delete();
    }
}