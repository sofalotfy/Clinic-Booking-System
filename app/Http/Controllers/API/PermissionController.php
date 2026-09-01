<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;
use App\Enums\AssistantPermissionsEnum;
use Illuminate\Http\Request;
use App\APIServices\Permissions\ListPermissions;
use App\APIServices\Permissions\ListAssistantPermissions;
use App\APIServices\Permissions\GetUserPermissions;

class PermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PERMISSIONS->value,
                only: ['index']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PERMISSIONS->value,
                only: ['listAssistantPermissions']
            ),
            new Middleware(
                'clinic.permission:' . AssistantPermissionsEnum::VIEW_ALL_PERMISSIONS->value,
                only: ['getUserPermissions']
            ),
        ];
    }

    public function index(Request $request)
    {
        return ListPermissions::execute($request);
    }

    public function listAssistantPermissions(Request $request)
    {
        return ListAssistantPermissions::execute($request);
    }

    public function GetUserPermissions(Request $request)
    {
        return GetUserPermissions::execute($request);
    }
}
