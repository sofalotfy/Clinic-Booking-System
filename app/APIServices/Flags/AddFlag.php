<?php

namespace App\APIServices\Flags;

use App\Models\Flag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class AddFlag
{
    public static function execute(Request $request): Flag
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191'],
            'color' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ])->validate();

        return Flag::create([
            'doctor_id' => $request->user()->doctor->id,
            'name' => $validated['name'],
            'color' => $validated['color'],
            'description' => $validated['description'] ?? null,
        ]);
    }
}