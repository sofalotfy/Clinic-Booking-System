<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\APIServices\Appointments\ListAppointments;
use App\APIServices\Appointments\BookAppointment;
use App\APIServices\Appointments\UpdateAppointment;
use App\APIServices\Appointments\BulkUpdateAppointments;
use Illuminate\Http\Request;
use App\Models\Appointment;

class AppointmentController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_APPOINTMENTS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_APPOINTMENT->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_APPOINTMENT->value . ',appointment',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_APPOINTMENT->value,
                only: ['bulkUpdate']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'appointments' => ListAppointments::execute($request),
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'appointment' => BookAppointment::execute($request),
        ]);
    }

    public function update(Request $request, Appointment $appointment)
    {
        return response()->json([
            'success' => true,
            'appointment' => UpdateAppointment::execute($request->user(), $appointment, $request->all()),
        ]);
    }

    public function bulkUpdate(Request $request)
    {
        return response()->json([
            'success' => true,
            'appointments' => BulkUpdateAppointments::execute($request),
        ]);
    }

}
