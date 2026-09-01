<?php

namespace App\APIServices\Notes;

use App\Models\Note;
use App\Services\Notes\UpdateNote as UpdateService;

class UpdateNote
{
    public static function execute($request, $note)
    {
        return UpdateService::execute($note, $request->note);
    }
}