<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Enums\AssistantPermissionsEnum;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\APIServices\Assistants\ListAssistants;
use App\APIServices\Assistants\ShowAssistant;
use App\APIServices\Assistants\StoreAssistant;
use App\APIServices\Assistants\UpdateAssistant;
use App\APIServices\Assistants\DeleteAssistant;
use App\APIServices\Assistants\AddRole;
use App\APIServices\Assistants\RemoveRole;
use Spatie\Permission\Models\Role;
use App\Models\Assistant;
use Illuminate\Http\Request;

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
        return response()->json([
            'assistants' => ListAssistants::execute($request->user()),
        ]);
    }

    public function show(Request $request, Assistant $assistant)
    {
        return response()->json([
            'assistant' => ShowAssistant::execute($assistant),
        ]);
    }

    public function store(Request $request)
    {
        return response()->json([
            'message' => 'Assistant created successfully.',
            'assistant' => StoreAssistant::execute($request),
        ], 201);
    }

    public function update(Request $request, Assistant $assistant)
    {
        return response()->json([
            'message' => 'Assistant updated successfully.',
            'assistant' => UpdateAssistant::execute($assistant, $request),
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
        return response()->json([
            'message' => 'Role assigned successfully.',
            'assistant' => AddRole::execute($assistant, $role),
        ]);
    }

    public function removeRole(Request $request, Assistant $assistant, Role $role)
    {
        return response()->json([
            'message' => 'Role removed successfully.',
            'assistant' => RemoveRole::execute($assistant, $role),
        ]);
    }
}