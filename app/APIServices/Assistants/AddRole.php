<?php

namespace App\APIServices\Assistants;

class AddRole
{
    public static function execute($assistant, $role)
    {
        $assistant->user->assignRole($role);
        
        return $assistant->load('user.roles');
    }
}