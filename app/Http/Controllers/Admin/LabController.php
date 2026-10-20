<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Labs\DeleteLab;
use App\AdminServices\Labs\ListLabs;
use App\AdminServices\Labs\ShowLab;
use App\AdminServices\Labs\StoreLab;
use App\AdminServices\Labs\UpdateLab;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class LabController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_LABS->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_LAB->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_LAB->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_LAB->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_LAB->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.labs.index', [
            'labs' => ListLabs::execute($request),
        ]);
    }

    public function create()
    {
        return view('admin.labs.create');
    }

    public function store(Request $request)
    {
        StoreLab::execute($request);

        return redirect()->route('web-admin.labs.index')
            ->with('status', 'Lab created successfully.');
    }

    public function show(Lab $lab)
    {
        return view('admin.labs.show', [
            'lab' => ShowLab::execute($lab),
        ]);
    }

    public function edit(Lab $lab)
    {
        return view('admin.labs.edit', [
            'lab' => ShowLab::execute($lab),
        ]);
    }

    public function update(Request $request, Lab $lab)
    {
        UpdateLab::execute($request, $lab);

        return redirect()->route('web-admin.labs.show', $lab->id)
            ->with('status', 'Lab updated successfully.');
    }

    public function destroy(Lab $lab)
    {
        DeleteLab::execute($lab);

        return redirect()->route('web-admin.labs.index')
            ->with('status', 'Lab deleted successfully.');
    }
}
