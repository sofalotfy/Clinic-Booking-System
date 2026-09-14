<?php

namespace App\APIServices\Clinics;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use App\Services\Clinics\UpdateClinic as UpdateService;
use App\Models\Clinic;

class UpdateClinic
{
    public static function execute(Request $request, Clinic $clinic)
    {
        $validated = Validator::make($request->all(), [
            'location_link' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string'],
            'instgram' => ['nullable', 'string'],
            'linkedin' => ['nullable', 'string'],
            'vezeeta' => ['nullable', 'string'],
        ])->validate();

        UpdateService::execute($clinic, $validated);
        return $clinic->fresh();
    }
}
