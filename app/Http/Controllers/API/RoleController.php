<?php

namespace App\Http\Controllers\API;

use Spatie\Permission\Models\Role;
use App\Models\Assistant;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Spatie\Permission\Models\Permission;
use App\Enums\UserType;
use App\Enums\AssistantPermissionsEnum;
use App\Services\Authentications\CheckClinicPermission;

class RoleController extends Controller
{
    public function index(Request $request)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::VIEW_ALL_ROLES->value
            ),
            403
        );

        $doctorId = $request->user()->type == UserType::DOCTOR
            ? $request->user()->doctor->id
            : $request->user()->assistant->doctor_id;

        return response()->json([
            'roles' => Role::with('permissions')
                ->where('doctor_id', $doctorId)
                ->get(),
        ]);
    }

    public function show(Request $request, Role $role)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::VIEW_SINGLE_ROLE->value,
                $role
            ),
            403
        );

        return response()->json([
            'role' => $role->load('permissions'),
        ]);
    }

    public function store(Request $request)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::CREATE_ROLE->value
            ),
            403
        );

        $doctorId = $request->user()->type == UserType::DOCTOR
            ? $request->user()->doctor->id
            : $request->user()->assistant->doctor_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
            'doctor_id' => $doctorId,
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return response()->json([
            'message' => 'Role created successfully.',
            'role' => $role->load('permissions'),
        ], 201);
    }

    public function update(Request $request, Role $role)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::UPDATE_ROLE->value,
                $role
            ),
            403
        );

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id',
        ]);

        $role->update([
            'name' => $validated['name'],
        ]);

        $role->syncPermissions($validated['permissions'] ?? []);

        return response()->json([
            'message' => 'Role updated successfully.',
            'role' => $role->load('permissions'),
        ]);
    }

    public function destroy(Request $request, Role $role)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::DELETE_ROLE->value,
                $role
            ),
            403
        );

        $role->delete();

        return response()->json([
            'message' => 'Role deleted successfully.',
        ]);
    }
}