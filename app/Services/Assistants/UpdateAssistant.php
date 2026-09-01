<?php

namespace App\Services\Assistants;

use App\Models\Assistant;
use Illuminate\Support\Facades\Hash;

class UpdateAssistant
{
    public static function execute(Assistant $assistant, array $data)
    {
        $assistant->user->update([
            'name' => $data['name'] ?? $assistant->user->name,
            'email' => $data['email'] ?? $assistant->user->email,
            'phone' => $data['phone'] ?? $assistant->user->phone,
        ]);

        if (!empty($data['password'])) {
            $assistant->user->update([
                'password' => Hash::make($data['password']),
            ]);
        }

        return ShowAssistant::execute($assistant);
    }
}