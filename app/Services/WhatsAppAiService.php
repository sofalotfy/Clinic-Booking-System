<?php

namespace App\Services;

use OpenAI\Laravel\Facades\OpenAI;

class WhatsAppAiService
{
    /**
     * Send user message with past conversation history to the AI model.
     *
     * @param string $userMessage
     * @param array $history Array of past messages: [['role' => 'user'|'assistant', 'content' => '...']]
     */
    public function ask(string $userMessage, array $history = []): string
    {
        // 1. Set system instruction defining persona and constraints
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful customer support assistant for a medical clinic. Keep responses concise, helpful, and friendly for WhatsApp.',
            ],
        ];

        // 2. Append past conversation history
        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        // 3. Append the newest incoming message
        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // 4. Send the full thread to Llama 3 / Groq
        $response = OpenAI::chat()->create([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $messages,
        ]);

        return $response->choices[0]->message->content;
    }
}