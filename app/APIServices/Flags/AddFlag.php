<?php

namespace App\APIServices\Flags;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Flags\StoreFlag;

class AddFlag
{
    public static function execute(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:191'],
            'color' => ['required', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ])->validate();

        return StoreFlag::execute($request->user(), $validated);
    }
}