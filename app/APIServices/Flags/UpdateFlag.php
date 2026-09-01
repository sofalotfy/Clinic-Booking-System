<?php

namespace App\APIServices\Flags;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Flags\UpdateFlag as UpdateService;
use App\Models\Flag;

class UpdateFlag
{
    public static function execute(Request $request, Flag $flag)
    {
        $validated = Validator::make($request->all(), [
            'name' => ['nullable', 'string', 'max:191'],
            'color' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ])->validate();

        UpdateService::execute($flag, $validated);
        return $flag->fresh();
    }
}