<?php

namespace App\Services\Notes;

use App\Models\Note;

class UpdateNote
{
    public static function execute($note, $text)
    {
        $note->update([
            "text" => $text,
        ]);

        return $note->refresh();
    }
}