<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\APIServices\Notes\UpdateNote;
use App\APIServices\Notes\DeleteNote;
use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_NOTE->value . ',note',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_NOTE->value . ',note',
                only: ['destroy']
            ),
        ];
    }

    public function update(Request $request, Note $note)
    {
        return response()->json([
            'success' => true,
            'note' => UpdateNote::execute($request, $note),
        ]);
    }

    public function destroy(Request $request, Note $note)
    {
        return response()->json([
            'success' => true,
            'message' => DeleteNote::execute($note),
        ]);
    }
}
