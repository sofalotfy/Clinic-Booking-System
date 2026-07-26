<?php

namespace App\Services\Assistants;

use App\Models\Assistant;
use Spatie\Permission\Models\Role;

class AssignAssistantRole
{
    public static function execute(Assistant $assistant, Role $role) {
        if ($assistant->doctor_id !== $role->doctor_id) {
            throw ValidationException::withMessages([
                'role' => 'This role does not belong to this doctor.',
            ]);
        }

        $assistant->user->assignRole($role);
    }
}