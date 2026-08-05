<?php

namespace App\APIServices\WhatsApp\AI;

use App\APIServices\WhatsApp\AI\Services\GetDoctorSlotsTool;
use OpenAI\Laravel\Facades\OpenAI;

class WhatsAppAiService
{
    protected array $tools = [
        'get_doctor_slots' => GetDoctorSlotsTool::class,
    ];

    public function ask(string $userMessage, array $history = []): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a helpful clinic assistant on WhatsApp. Today is ' . date('Y-m-d') . '. Use tools when necessary to query information before answering.',
            ],
        ];

        foreach ($history as $msg) {
            $messages[] = [
                'role' => $msg['role'],
                'content' => $msg['content'],
            ];
        }

        $messages[] = [
            'role' => 'user',
            'content' => $userMessage,
        ];

        // Format tools array for OpenAI / Groq API
        $toolSchemas = array_map(fn($class) => $class::definition(), $this->tools);

        // First Call: Ask AI (providing available tools)
        $response = OpenAI::chat()->create([
            'model' => 'llama-3.3-70b-versatile',
            'messages' => $messages,
            'tools' => $toolSchemas,
            'tool_choice' => 'auto',
        ]);

        $responseMessage = $response->choices[0]->message;

        // Check if AI requested a tool execution
        if (!empty($responseMessage->toolCalls)) {
            // Append AI's intent to call the tool
            $messages[] = $responseMessage->toArray();

            foreach ($responseMessage->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments, true);

                if (isset($this->tools[$functionName])) {
                    // Execute tool logic
                    $result = $this->tools[$functionName]::handle($arguments);

                    // Append tool output back to conversation thread
                    $messages[] = [
                        'role' => 'tool',
                        'tool_call_id' => $toolCall->id,
                        'content' => json_encode($result),
                    ];
                }
            }

            // Second Call: Get final natural response from AI with tool results included
            $finalResponse = OpenAI::chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => $messages,
            ]);

            return $finalResponse->choices[0]->message->content;
        }

        return $responseMessage->content;
    }
}