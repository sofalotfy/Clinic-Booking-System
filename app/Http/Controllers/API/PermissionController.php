<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\AssistantPermissionsEnum;
use Spatie\Permission\Models\Permission;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;


class PermissionController extends Controller
{
    // public function __construct()
    // {
    // }

    public function index(Request $request)
    {
        //check permission
        return;
        return response()->json([
            'permissions' => Permission::select('id', 'name', 'type')->get(),
        ]);
    }

    public function listAssistantPermissions(Request $request)
    {
        if ($request->user()->type != UserType::DOCTOR) {
            throw new \Symfony\Component\HttpKernel\Exception\HttpException(
                403,
                'Only doctors can perform this action.'
            );
        }

        return response()->json([
            'permissions' => Permission::select('id', 'name')->where('type', PermissionsTypeEnum::ASSISTANT)->get(),
        ]);
    }

    public function GetUserPermissions(Request $request)
    {
        $user = $request->user();
        $userPermissions = $user->getAllPermissions()->pluck('name','id')->toArray();
        return response()->json([
            'user_permissions' => $userPermissions,
        ]);
    }
}
