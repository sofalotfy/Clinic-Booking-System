<?php

namespace App\AdminServices\Admins;

use App\Models\User;
use App\Services\Admins\DeleteAdmin as DeleteAdminService;

class DeleteAdmin
{
    public static function execute($request, User $admin)
    {
        return DeleteAdminService::execute($request->user(), $admin);
    }
}
