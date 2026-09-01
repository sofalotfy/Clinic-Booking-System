<?php

namespace App\APIServices\Roles;

use Spatie\Permission\Models\Role;
use App\Services\Roles\DeleteRole as DeleteService;

class DeleteRole
{
    public static function execute($request, Role $role)
    {
        return response()->json([
            "success" => DeleteService::execute($request->user(), $role),
        ]);
    }
}