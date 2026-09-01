<?php

namespace App\Services\Notes;

use App\Models\Note;

class DeleteNote
{
    public static function execute(Note $note)
    {
        return $note->delete();
    }
}