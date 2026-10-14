<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\Admins\DeleteAdmin;
use App\AdminServices\Admins\ListAdmins;
use App\AdminServices\Admins\ShowAdmin;
use App\AdminServices\Admins\StoreAdmin;
use App\AdminServices\Admins\UpdateAdmin;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_ADMINS->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_ADMIN->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_ADMIN->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_ADMIN->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_ADMIN->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.admins.index', [
            'admins' => ListAdmins::execute($request),
        ]);
    }

    public function create()
    {
        return view('admin.admins.create', [
            'roles' => \App\AdminServices\AdminRoles\ListRoles::options(),
        ]);
    }

    public function store(Request $request)
    {
        StoreAdmin::execute($request);

        return redirect()->route('web-admin.admins.index')
            ->with('status', 'Admin created successfully.');
    }

    public function show(User $admin)
    {
        return view('admin.admins.show', [
            'admin' => ShowAdmin::execute($admin),
        ]);
    }

    public function edit(Request $request, User $admin)
    {
        return view('admin.admins.edit', [
            'admin' => ShowAdmin::execute($admin),
            'roles' => \App\AdminServices\AdminRoles\ListRoles::options(),
            'editingSelf' => $request->user()->id === $admin->id,
        ]);
    }

    public function update(Request $request, User $admin)
    {
        UpdateAdmin::execute($request, $admin);

        return redirect()->route('web-admin.admins.show', $admin->id)
            ->with('status', 'Admin updated successfully.');
    }

    public function destroy(Request $request, User $admin)
    {
        if ($request->user()->is($admin)) {
            return redirect()->route('web-admin.admins.index')
                ->withErrors(['admin' => 'You cannot delete your own account.']);
        }

        DeleteAdmin::execute($request, $admin);

        return redirect()->route('web-admin.admins.index')
            ->with('status', 'Admin deleted successfully.');
    }
}
