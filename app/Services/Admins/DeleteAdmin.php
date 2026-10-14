<?php

namespace App\Services\Admins;

use App\Models\User;

class DeleteAdmin
{
    public static function execute(User $actor, User $admin)
    {
        abort_if($actor->is($admin), 403, 'You cannot delete your own account.');

        $admin->roles()->detach();

        return $admin->delete();
    }
}
