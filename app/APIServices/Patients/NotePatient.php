<?php

namespace App\APIServices\Patients;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Notes\NotePatient as NoteService;
use App\Models\Patient;

class NotePatient
{
    public static function execute(Request $request, Patient $patient)
    {
        $validated = Validator::make($request->all(), [
            'note' => 'required',
        ])->validate();

        $note = NoteService::execute($request->user(), $patient, $validated['note']);

        
        return response()->json([
            "note" => $note,
        ]);
    }
}