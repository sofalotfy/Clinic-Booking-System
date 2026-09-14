<?php

namespace App\APIServices\Clinics;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Clinics\StoreClinic;

class AddClinic
{
    public static function execute(Request $request)
    {
        $validated = Validator::make($request->all(), [
            'location_link' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string'],
            'instgram' => ['nullable', 'string'],
            'linkedin' => ['nullable', 'string'],
            'vezeeta' => ['nullable', 'string'],
        ])->validate();

        return StoreClinic::execute($request->user(), $validated);
    }
}
