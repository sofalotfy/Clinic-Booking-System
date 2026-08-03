<?php

namespace App\APIServices\Assistants;

class RemoveRole
{
    public static function execute($assistant, $role)
    {
        $assistant->user->removeRole($role);
        return $assistant->load('user.roles');
    }
}