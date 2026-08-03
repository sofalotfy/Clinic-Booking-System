<?php

namespace App\APIServices\Assistants;

class AddRole
{
    public static function execute($assistant, $role)
    {
        abort_if(
            $assistant->doctor_id != $role->doctor_id, 
            403, 
            'Role does not belong to the same doctor.'
        );

        $assistant->user->assignRole($role);
        return $assistant->load('user.roles');
    }
}