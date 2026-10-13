<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\AdminPermissions\ListPermissions;
use App\AdminServices\AdminRoles\DeleteRole;
use App\AdminServices\AdminRoles\ListRoles;
use App\AdminServices\AdminRoles\ShowRole;
use App\AdminServices\AdminRoles\StoreRole;
use App\AdminServices\AdminRoles\UpdateRole;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminRoleController extends Controller implements HasMiddleware
{
    public const SUPER_ADMIN = 'Super Admin';

    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_ROLES->value, only: ['index']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_SINGLE_ROLE->value, only: ['show']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::CREATE_ROLE->value, only: ['create', 'store']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::UPDATE_ROLE->value, only: ['edit', 'update']),
            new Middleware('admin.permission:'.AdminPermissionsEnum::DELETE_ROLE->value, only: ['destroy']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.roles.index', [
            'roles' => ListRoles::execute($request),
        ]);
    }

    public function create(Request $request)
    {
        return view('admin.roles.create', [
            'permissions' => ListPermissions::execute($request),
        ]);
    }

    public function store(Request $request)
    {
        StoreRole::execute($request);

        return redirect()->route('web-admin.roles.index')
            ->with('status', 'Role created successfully.');
    }

    public function show(Role $adminRole)
    {
        return view('admin.roles.show', [
            'role' => ShowRole::execute($adminRole),
        ]);
    }

    public function edit(Request $request, Role $adminRole)
    {
        abort_if($adminRole->name === self::SUPER_ADMIN, 403, 'The Super Admin role cannot be edited.');

        return view('admin.roles.edit', [
            'role' => ShowRole::execute($adminRole),
            'permissions' => ListPermissions::execute($request),
        ]);
    }

    public function update(Request $request, Role $adminRole)
    {
        abort_if($adminRole->name === self::SUPER_ADMIN, 403, 'The Super Admin role cannot be edited.');

        UpdateRole::execute($request, $adminRole);

        return redirect()->route('web-admin.roles.show', $adminRole->id)
            ->with('status', 'Role updated successfully.');
    }

    public function destroy(Role $adminRole)
    {
        abort_if($adminRole->name === self::SUPER_ADMIN, 403, 'The Super Admin role cannot be deleted.');

        DeleteRole::execute($adminRole);

        return redirect()->route('web-admin.roles.index')
            ->with('status', 'Role deleted successfully.');
    }
}
