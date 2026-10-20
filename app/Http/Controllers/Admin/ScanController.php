<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Scans\DeleteScan;
use App\AdminServices\Scans\ListScans;
use App\AdminServices\Scans\ShowScan;
use App\AdminServices\Scans\StoreScan;
use App\AdminServices\Scans\UpdateScan;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Scan;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class ScanController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_SCANS->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_SCAN->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_SCAN->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_SCAN->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_SCAN->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.scans.index', [
            'scans' => ListScans::execute($request),
        ]);
    }

    public function create()
    {
        return view('admin.scans.create');
    }

    public function store(Request $request)
    {
        StoreScan::execute($request);

        return redirect()->route('web-admin.scans.index')
            ->with('status', 'Scan created successfully.');
    }

    public function show(Scan $scan)
    {
        return view('admin.scans.show', [
            'scan' => ShowScan::execute($scan),
        ]);
    }

    public function edit(Scan $scan)
    {
        return view('admin.scans.edit', [
            'scan' => ShowScan::execute($scan),
        ]);
    }

    public function update(Request $request, Scan $scan)
    {
        UpdateScan::execute($request, $scan);

        return redirect()->route('web-admin.scans.show', $scan->id)
            ->with('status', 'Scan updated successfully.');
    }

    public function destroy(Scan $scan)
    {
        DeleteScan::execute($scan);

        return redirect()->route('web-admin.scans.index')
            ->with('status', 'Scan deleted successfully.');
    }
}
