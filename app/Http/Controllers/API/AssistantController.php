<?php

namespace App\Http\Controllers\API;

use Spatie\Permission\Models\Role;
use App\Models\User;
use App\Models\Assistant;
use Illuminate\Http\Request;
use App\Enums\UserType;
use Illuminate\Validation\Rule;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Enums\AssistantPermissionsEnum;
use App\Services\Authentications\CheckClinicPermission;

class AssistantController extends Controller
{
    public function index(Request $request)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::VIEW_ALL_ASSISTANTS->value
            ),
            403
        );

        $doctorId = $request->user()->type == UserType::DOCTOR
            ? $request->user()->doctor->id
            : $request->user()->assistant->doctor_id;

        $assistants = Assistant::where('doctor_id', $doctorId)
            ->with([
                'user.roles:id,name',
                'user:id,name,email,phone'
            ])
            ->get();

        return response()->json([
            'assistants' => $assistants,
        ]);
    }

    public function show(Request $request, Assistant $assistant)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::VIEW_SINGLE_ASSISTANT->value,
                $assistant
            ),
            403
        );

        $assistant->load([
            'user.roles:id,name',
            'user:id,name,email,phone'
        ]);

        return response()->json([
            'assistant' => $assistant,
        ]);
    }

    public function store(Request $request)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::CREATE_ASSISTANT->value
            ),
            403
        );

        $doctorId = $request->user()->type == UserType::DOCTOR
            ? $request->user()->doctor->id
            : $request->user()->assistant->doctor_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|max:20|unique:users,phone',
            'password' => 'required|string|min:8',
            'role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(function ($query) use ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                }),
            ],
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
            'password' => Hash::make($validated['password']),
            'type' => UserType::ASSISTANT,
        ]);

        $assistant = Assistant::create([
            'user_id' => $user->id,
            'doctor_id' => $doctorId,
        ]);

        if (!empty($validated['role_id'])) {
            $role = Role::findOrFail($validated['role_id']);
            $user->syncRoles([$role]);
        }

        return response()->json([
            'message' => 'Assistant created successfully.',
            'assistant' => $assistant->load('user.roles'),
        ], 201);
    }

    public function update(Request $request, Assistant $assistant)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::UPDATE_ASSISTANT->value,
                $assistant
            ),
            403
        );

        $doctorId = $assistant->doctor_id;

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => [
                'required',
                'email',
                Rule::unique('users')->ignore($assistant->user_id),
            ],
            'phone' => [
                'required',
                Rule::unique('users', 'phone')->ignore($assistant->user_id),
            ],
            'password' => 'nullable|string|min:8',
            'role_id' => [
                'nullable',
                Rule::exists('roles', 'id')->where(function ($query) use ($doctorId) {
                    $query->where('doctor_id', $doctorId);
                }),
            ],
        ]);

        $assistant->user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'],
        ]);

        if (!empty($validated['password'])) {
            $assistant->user->update([
                'password' => Hash::make($validated['password']),
            ]);
        }

        if (array_key_exists('role_id', $validated)) {
            if ($validated['role_id']) {
                $assistant->user->syncRoles([$validated['role_id']]);
            } else {
                $assistant->user->syncRoles([]);
            }
        }

        return response()->json([
            'message' => 'Assistant updated successfully.',
            'assistant' => $assistant->load('user.roles'),
        ]);
    }

    public function destroy(Request $request, Assistant $assistant)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::DELETE_ASSISTANT->value,
                $assistant
            ),
            403
        );

        $assistant->user->delete();

        return response()->json([
            'message' => 'Assistant deleted successfully.',
        ]);
    }
    
    public function assignRole(Request $request, Assistant $assistant, Role $role)
    {
        abort_if(
            ! CheckClinicPermission::execute(
                $request,
                AssistantPermissionsEnum::MANAGE_ASSISTANT_ROLES->value,
                $assistant
            ),
            403
        );

        abort_if(
            $assistant->doctor_id != $role->doctor_id,
            403,
            'Role does not belong to the same doctor.'
        );

        $assistant->user->syncRoles([$role]);

        return response()->json([
            'message' => 'Role assigned successfully.',
        ]);
    }
}