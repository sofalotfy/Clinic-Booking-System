<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Enums\AssistantPermissionsEnum;
use App\Http\Controllers\Controller;
use App\APIServices\Roles\ListRoles;
use App\APIServices\Roles\ShowRole;
use App\APIServices\Roles\StoreRole;
use App\APIServices\Roles\UpdateRole;
use App\APIServices\Roles\DeleteRole;
use Spatie\Permission\Models\Role;

class RoleController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_ROLES->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_ROLE->value . ',role',
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_ROLE->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_ROLE->value . ',role',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_ROLE->value . ',role',
                only: ['destroy']
            ),
        ];
    }
    
    public function index(Request $request)
    {
        return ListRoles::execute($request);
    }

    public function show(Request $request, Role $role)
    {
        return ShowRole::execute($request, $role);
    }

    public function store(Request $request)
    {
        return StoreRole::execute($request);
    }

    public function update(Request $request, Role $role)
    {
        return UpdateRole::execute($request, $role);
    }

    public function destroy(Request $request, Role $role)
    {
        return DeleteRole::execute($request, $role);
    }
}