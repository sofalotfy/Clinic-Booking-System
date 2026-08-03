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
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\APIServices\Assistants\ListAssistants;
use App\APIServices\Assistants\ShowAssistant;
use App\APIServices\Assistants\StoreAssistant;
use App\APIServices\Assistants\UpdateAssistant;
use App\APIServices\Assistants\DeleteAssistant;
use App\APIServices\Assistants\AddRole;
use App\APIServices\Assistants\RemoveRole;

class AssistantController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_ASSISTANTS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_SINGLE_ASSISTANT->value . ',assistant',
                only: ['show']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_ASSISTANT->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_ASSISTANT->value . ',assistant',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_ASSISTANT->value . ',assistant',
                only: ['destroy']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::MANAGE_ASSISTANT_ROLES->value . ',assistant',
                only: ['assignRole']
            ),
        ];
    }

    public function index(Request $request)
    {
        $assistants = ListAssistants::execute($request->user());

        return response()->json([
            'assistants' => $assistants,
        ]);
    }

    public function show(Request $request, Assistant $assistant)
    {

        $assistant = ShowAssistant::execute($assistant);

        return response()->json([
            'assistant' => $assistant,
        ]);
    }

    public function store(Request $request)
    {
        $assistant = StoreAssistant::execute($request);

        return response()->json([
            'message' => 'Assistant created successfully.',
            'assistant' => $assistant,
        ], 201);
    }

    public function update(Request $request, Assistant $assistant)
    {
        $assistant = UpdateAssistant::execute($assistant, $request);

        return response()->json([
            'message' => 'Assistant updated successfully.',
            'assistant' => $assistant->load('user.roles'),
        ]);
    }

    public function destroy(Request $request, Assistant $assistant)
    {
        DeleteAssistant::execute($assistant);

        return response()->json([
            'message' => 'Assistant deleted successfully.',
        ]);
    }
    
    public function assignRole(Request $request, Assistant $assistant, Role $role)
    {
        $assistant = AddRole::execute($assistant, $role);
        
        return response()->json([
            'message' => 'Role assigned successfully.',
            'assistant' => $assistant,
        ]);
    }

    public function removeRole(Request $request, Assistant $assistant, Role $role)
    {
        $assistant = RemoveRole::execute($assistant, $role);

        return response()->json([
            'message' => 'Role removed successfully.',
            'assistant' => $assistant,
        ]);
    }
}