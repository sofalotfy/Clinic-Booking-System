<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use App\Enums\AssistantPermissionsEnum;
use App\APIServices\Doctors\UpdateDoctor;
use Illuminate\Http\Request;
use App\Models\Doctor;

class DoctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_DOCTOR->value . ',doctor',
                only: ['update']
            ),
        ];
    }

    public function update(Request $request, Doctor $doctor)
    {
        return response()->json([
            'message' => 'Doctor profile updated successfully.',
            'doctor' => UpdateDoctor::execute($request, $doctor),
        ]);
    }
}
