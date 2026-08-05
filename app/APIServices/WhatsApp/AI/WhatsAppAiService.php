<?php

namespace App\APIServices\WhatsApp\AI;

use App\APIServices\WhatsApp\AI\Tools\GetDoctorSlotsTool;
use App\APIServices\WhatsApp\AI\Tools\SearchDoctorTool;
use OpenAI\Laravel\Facades\OpenAI;

class WhatsAppAiService
{
    protected array $tools = [
        'get_doctor_slots' => GetDoctorSlotsTool::class,
        'search_doctor'    => SearchDoctorTool::class,
    ];

    public function ask(string $userMessage, array $history = []): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => 'You are a clinic support assistant on WhatsApp. Today is ' . date('Y-m-d') . '. Use tools to look up doctors, schedules, or appointments as needed before answering.',
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

        $toolSchemas = array_map(fn($class) => $class::definition(), $this->tools);

        // Cap the maximum iterations to prevent infinite API loops
        $maxTurns = 5;
        $turns = 0;

        while ($turns < $maxTurns) {
            $turns++;

            // Call model with current message thread
            $response = OpenAI::chat()->create([
                'model' => 'llama-3.3-70b-versatile',
                'messages' => $messages,
                'tools' => $toolSchemas,
                'tool_choice' => 'auto',
            ]);

            $responseMessage = $response->choices[0]->message;

            // If the model DID NOT request any tools, it returned the final text answer
            if (empty($responseMessage->toolCalls)) {
                return $responseMessage->content;
            }

            // Append the AI's tool request intention to the conversation thread
            $messages[] = $responseMessage->toArray();

            // Execute ALL tool calls requested in this turn (handles parallel calls)
            foreach ($responseMessage->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments, true) ?? [];

                $result = ['error' => 'Tool not found'];

                if (isset($this->tools[$functionName])) {
                    try {
                        $result = $this->tools[$functionName]::handle($arguments);
                    } catch (\Exception $e) {
                        $result = ['error' => $e->getMessage()];
                    }
                }

                // Append each individual tool output back to the thread
                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => json_encode($result),
                ];
            }

            // Loop continues -> AI receives tool results and decides whether to write
            // a final response or execute another tool!
        }

        return "I'm having trouble retrieving all the required details right now. Please try again.";
    }
}