<?php

namespace App\Http\Controllers\API;

use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use App\Http\Controllers\Controller;
use App\Enums\AssistantPermissionsEnum;
use App\APIServices\Flags\ListFlags;
use App\APIServices\Flags\AddFlag;
use App\APIServices\Flags\UpdateFlag;
use App\APIServices\Flags\DeleteFlag;
use Illuminate\Http\Request;
use App\Models\Flag;

class FlagController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_FLAGS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::CREATE_FLAG->value,
                only: ['store']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::UPDATE_FLAG->value . ',flag',
                only: ['update']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::DELETE_FLAG->value . ',flag',
                only: ['destroy']
            ),
        ];
    }

    public function index(Request $request)
    {
        return response()->json([
            'success' => true,
            'flags' => ListFlags::execute($request),
        ]);
    }
    public function store(Request $request)
    {
        return response()->json([
            'success' => true,
            'flag' => AddFlag::execute($request),
        ]);
    }

    public function update(Request $request, Flag $flag)
    {        
        return response()->json([
            'success' => true,
            'flag' => UpdateFlag::execute($request, $flag),
        ]);
    }

    public function destroy(Flag $flag)
    {
        DeleteFlag::execute($flag);
        
        return response()->json([
            'success' => true,
            'message' => 'Flag deleted successfully',
        ]);
    }
}
