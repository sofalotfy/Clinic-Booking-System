<?php

namespace App\Services\Assistants;

use App\Models\Assistant;

class ListAssistants
{
    public static function execute($user)
    {
        return Assistant::where('doctor_id', $user->clinicDoctor()->id)
            ->with([
                'user.roles:id,name',
                'user:id,name,email,phone'
            ]);
    }
}