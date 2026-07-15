<?php

namespace App\APIServices\Notes;

use App\Models\Note;

class EditNote
{
    public static function execute($noteId,  $note)
    {
        // if ($note->doctor_id !== auth()->user()->doctor->id) {
        //     throw new \Exception('You are not authorized to edit this note');
        // }

        $note->update([
            'text' => $note,
        ]);

        return $note;
    }
}