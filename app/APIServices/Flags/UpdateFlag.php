<?php

namespace App\APIServices\Flags;

use App\Models\Flag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class UpdateFlag
{
    public static function execute(Request $request): Flag
    {
        $validated = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:191'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ])->validate();

        $flag->update([
            'name' => $validated['name'] ?? $flag->name,
            'color' => $validated['color'] ?? $flag->color,
            'description' => $validated['description'] ?? $flag->description,
        ]);

        return $flag->refresh();
    }
}