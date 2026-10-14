<?php

namespace App\AdminServices\AdminRoles;

use App\Models\Role;
use App\Services\AdminRoles\DeleteRole as DeleteRoleService;

class DeleteRole
{
    public static function execute(Role $role)
    {
        return DeleteRoleService::execute($role);
    }
}
