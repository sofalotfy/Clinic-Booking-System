<?php

namespace App\Http\Controllers\Admin;

use App\AdminServices\AdminPermissions\ListPermissions;
use App\Enums\AdminPermissionsEnum;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;

class AdminPermissionController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return [
            new Middleware('admin.permission:'.AdminPermissionsEnum::VIEW_ALL_PERMISSIONS->value, only: ['index']),
        ];
    }

    public function index(Request $request)
    {
        return view('admin.permissions.index', [
            'permissions' => ListPermissions::execute($request),
        ]);
    }
}
