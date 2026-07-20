<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Enums\PermissionsEnum;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $userPermissions = $user->getAllPermissions()->pluck('name','id')->toArray();
        return response()->json([
            'permissions' => Permission::all(),
            'user_permissions' => $userPermissions,
        ]);
    }

    public function update(Request $request, $id)
    {
        $permission = Permission::find($id);
        $permission->name = $request->name;
        $permission->save();
        return response()->json([
            'message' => 'Permission updated successfully.',
            'permission' => $permission,
        ]);
    }
    public function store(Request $request)
    {
        $permission = Permission::create($request->all());
        return response()->json([
            'message' => 'Permission created successfully.',
            'permission' => $permission,
        ]);
    }
    public function destroy(Request $request, $id)
    {
        $permission = Permission::find($id);
        $permission->delete();
        return response()->json([
            'message' => 'Permission deleted successfully.',
        ]);
    }
    public function show(Request $request, $id)
    {
        $permission = Permission::find($id);
        return response()->json([
            'permission' => $permission,
        ]);
    }
}
