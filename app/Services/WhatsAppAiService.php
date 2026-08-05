<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class WhatsAppAiService
{
    public function ask(string $userMessage): string
    {
        $response = OpenAI::chat()->create([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => 'You are a helpful customer support assistant for a clinic.',
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage,
                ],
            ],
        ]);

        return $response->choices[0]->message->content;
    }
}