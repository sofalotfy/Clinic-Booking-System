<?php

namespace App\APIServices\Notes;

use App\Models\Note;

class EditNote
{
    public static function execute($noteId,  $text)
    {
        $note = Note::find($noteId);

        if ($note->doctor_id != auth()->user()->doctor->id) {
            throw new \Exception("You are not authorized to edit this note my id {$note->doctor_id} doctor id " . auth()->user()->doctor->id);
        }

        $note->update([
            'text' => $text,
        ]);

        return $note;
    }
}