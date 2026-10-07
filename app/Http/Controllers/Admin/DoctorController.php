<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Doctors\DeleteDoctor;
use App\AdminServices\Doctors\ListDoctors;
use App\AdminServices\Doctors\ShowDoctor;
use App\AdminServices\Doctors\StoreDoctor;
use App\AdminServices\Doctors\UpdateDoctor;
use App\Http\Controllers\Controller;
use App\Models\Doctor;
use Illuminate\Http\Request;

class DoctorController extends Controller
{
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
            'doctor' => ShowDoctor::execute($doctor->id),
        ]);
    }

    public function edit(Doctor $doctor)
    {
        return view('admin.doctors.edit', [
            'doctor' => ShowDoctor::execute($doctor->id),
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
        DeleteDoctor::execute($doctor->id);

        return redirect()->route('web-admin.doctors.index')
            ->with('status', 'Doctor deleted successfully.');
    }
}
