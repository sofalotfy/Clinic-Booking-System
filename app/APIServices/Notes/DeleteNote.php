<?php

namespace App\APIServices\Notes;

use App\Models\Note;
use App\Services\Notes\DeleteNote as DeleteService;

class DeleteNote
{
    public static function execute($note)
    {
        return DeleteService::execute($note);
    }
}