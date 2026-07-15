<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Notes\NotePatient;

class NoteController extends Controller
{
    public function notePatient(Request $request)
    {
        $note = NotePatient::execute($request->patient_id, $request->note);

        return response()->json([
            'success' => true,
            'note' => $note,
        ]);
    }

    public function editNote(Request $request, $noteId)
    {
        $note = EditNote::execute($noteId, $request->note);

        return response()->json([
            'success' => true,
            'note' => $note,
        ]);
    }

    public function deleteNote(Request $request, $noteId)
    {
        $note = DeleteNote::execute($noteId);

        return response()->json([
            'success' => true,
            'message' => 'Note deleted successfully',
        ]);
    }
}
