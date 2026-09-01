<?php

namespace App\APIServices\Assistants;

use App\Models\Assistant;
use Illuminate\Validation\Rule;
use App\Services\Assistants\UpdateAssistant as UpdateService;

class UpdateAssistant
{
    public static function execute(Assistant $assistant, $request)
    {
        $validated = $request->validate([
            'name' => 'nullable|string|max:255',
            'email' => [
                'nullable',
                'email',
                Rule::unique('users')->ignore($assistant->user_id),
            ],
            'phone' => [
                'nullable',
                Rule::unique('users', 'phone')->ignore($assistant->user_id),
            ],
            'password' => 'nullable|string|min:8',
        ]);

        return UpdateService::execute($assistant, $validated);
    }
}