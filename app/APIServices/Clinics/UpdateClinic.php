<?php

namespace App\APIServices\Clinics;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;
use App\Services\Clinics\UpdateClinic as UpdateService;
use App\Models\Clinic;

class UpdateClinic
{
    public static function execute(Request $request, Clinic $clinic)
    {
        $validated = Validator::make($request->all(), [
            'name' => ['nullable', 'string'],
            'location_link' => ['nullable', 'string'],
            'address' => ['nullable', 'string'],
            'facebook' => ['nullable', 'string'],
            'instgram' => ['nullable', 'string'],
            'linkedin' => ['nullable', 'string'],
            'vezeeta' => ['nullable', 'string'],
            'notifications_phone' => ['nullable', 'string'],
            'description' => ['nullable', 'string'],
        ])->validate();

        if (array_key_exists('description', $validated)) {
            $clinic->doctor->update(['description' => $validated['description']]);
        }

        UpdateService::execute($clinic, Arr::except($validated, 'description'));

        $clinic = $clinic->fresh('doctor');
        $clinic->description = $clinic->doctor?->description;

        return $clinic->makeHidden('doctor');
    }
}