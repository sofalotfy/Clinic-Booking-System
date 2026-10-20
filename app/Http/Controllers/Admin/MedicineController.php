<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Medicines\DeleteMedicine;
use App\AdminServices\Medicines\ListMedicines;
use App\AdminServices\Medicines\ShowMedicine;
use App\AdminServices\Medicines\StoreMedicine;
use App\AdminServices\Medicines\UpdateMedicine;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Medicine;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class MedicineController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_MEDICINES->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_MEDICINE->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_MEDICINE->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_MEDICINE->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_MEDICINE->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.medicines.index', [
            'medicines' => ListMedicines::execute($request),
        ]);
    }

    public function create()
    {
        return view('admin.medicines.create');
    }

    public function store(Request $request)
    {
        StoreMedicine::execute($request);

        return redirect()->route('web-admin.medicines.index')
            ->with('status', 'Medicine created successfully.');
    }

    public function show(Medicine $medicine)
    {
        return view('admin.medicines.show', [
            'medicine' => ShowMedicine::execute($medicine),
        ]);
    }

    public function edit(Medicine $medicine)
    {
        return view('admin.medicines.edit', [
            'medicine' => ShowMedicine::execute($medicine),
        ]);
    }

    public function update(Request $request, Medicine $medicine)
    {
        UpdateMedicine::execute($request, $medicine);

        return redirect()->route('web-admin.medicines.show', $medicine->id)
            ->with('status', 'Medicine updated successfully.');
    }

    public function destroy(Medicine $medicine)
    {
        DeleteMedicine::execute($medicine);

        return redirect()->route('web-admin.medicines.index')
            ->with('status', 'Medicine deleted successfully.');
    }
}
