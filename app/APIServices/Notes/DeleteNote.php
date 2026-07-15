<?php

namespace App\APIServices\Notes;

use App\Models\Note;

class DeleteNote
{
    public static function execute($noteId)
    {
        $note = Note::find($noteId);

        if ($note->doctor_id !== auth()->user()->doctor->id) {
            throw new \Exception('You are not authorized to delete this note');
        }

        return $note->delete();
    }
}