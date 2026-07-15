<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\APIServices\Notes\NotePatient;

class NoteController extends Controller
{
    public function notePatient(Request $request)
    {
        $note = NotePatient::execute($request->note, $request->patient_id);

        return response()->json([
            'success' => true,
            'note' => $note,
        ]);
    }
}
