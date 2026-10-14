<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Doctors\ListDoctors;
use App\AdminServices\Doctors\ReinstateDoctor;
use App\AdminServices\Doctors\ShowDoctor;
use App\AdminServices\Doctors\StoreDoctor;
use App\AdminServices\Doctors\SuspendDoctor;
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
            new Middleware('admin.permission:'.AdminPermissionsEnum::SUSPEND_DOCTOR->value, only: ['destroy']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::REINSTATE_DOCTOR->value, only: ['reinstate']),
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

    /**
     * Doctors are never hard deleted from the admin panel, they are suspended
     * so their data is kept and they can be reinstated later.
     */
    public function destroy(Request $request, Doctor $doctor)
    {
        SuspendDoctor::execute($request, $doctor);

        return back()->with('status', 'Doctor suspended successfully.');
    }

    public function reinstate(Request $request, Doctor $doctor)
    {
        ReinstateDoctor::execute($request, $doctor);

        return back()->with('status', 'Doctor reinstated successfully.');
    }
}
