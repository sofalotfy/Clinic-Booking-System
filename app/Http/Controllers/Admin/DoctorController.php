<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Doctors\DeleteDoctor;
use App\AdminServices\Doctors\ListDoctors;
use App\AdminServices\Doctors\ShowDoctor;
use App\AdminServices\Doctors\StoreDoctor;
use App\AdminServices\Doctors\UpdateDoctor;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class DoctorController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_DOCTORS->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_DOCTOR->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_DOCTOR->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_DOCTOR->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_DOCTOR->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.doctors.index', [
            'doctors' => ListDoctors::execute($request),
        ]);
    }

    public function create()
    {
        return view('admin.doctors.create');
    }

    public function store(Request $request)
    {
        StoreDoctor::execute($request);

        return redirect()->route('web-admin.doctors.index')
            ->with('status', 'Doctor created successfully.');
    }

    public function show(Doctor $doctor)
    {
        return view('admin.doctors.show', [
            'doctor' => ShowDoctor::execute($doctor),
        ]);
    }

    public function edit(Doctor $doctor)
    {
        return view('admin.doctors.edit', [
            'doctor' => ShowDoctor::execute($doctor),
        ]);
    }

    public function update(Request $request, Doctor $doctor)
    {
        UpdateDoctor::execute($request, $doctor);

        return redirect()->route('web-admin.doctors.show', $doctor->id)
            ->with('status', 'Doctor updated successfully.');
    }

    public function destroy(Doctor $doctor)
    {
        DeleteDoctor::execute($doctor);

        return redirect()->route('web-admin.doctors.index')
            ->with('status', 'Doctor deleted successfully.');
    }
}
