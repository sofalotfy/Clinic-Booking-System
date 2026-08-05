<?php

namespace App\APIServices\WhatsApp\AI;

use App\APIServices\WhatsApp\AI\Services\GetDoctorSlotsTool;
use App\APIServices\WhatsApp\AI\Services\GetDoctorAvailableDays;
use OpenAI\Laravel\Facades\OpenAI;
use Carbon\Carbon;

class WhatsAppAiService
{
    protected array $tools = [
        'get_doctor_slots' => GetDoctorSlotsTool::class,
        'get_doctor_available_days' => GetDoctorAvailableDays::class,
    ];

    /**
     * @param string $userMessage
     * @param array $history Past chat messages
     * @param array $context Contextual details about patient, doctor, and clinic
     */
    public function ask(string $userMessage, array $history = [], array $context = []): string
    {
        // 1. Build System Instruction with full context
        $systemContent = $this->buildSystemPrompt($context);

        $messages = [
            [
                'role' => 'system',
                'content' => $systemContent,
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

        $toolSchemas = array_values(array_map(fn($class) => $class::definition(), $this->tools));

        $maxTurns = 5;
        $turns = 0;

        while ($turns < $maxTurns) {
            $turns++;

            $payload = [
                'model' => 'llama-3.3-70b-versatile',
                'messages' => $messages,
            ];

            if (!empty($toolSchemas)) {
                $payload['tools'] = $toolSchemas;
                $payload['tool_choice'] = 'auto';
            }

            $response = OpenAI::chat()->create($payload);
            $responseMessage = $response->choices[0]->message;

            if (empty($responseMessage->toolCalls)) {
                return $responseMessage->content;
            }

            $messages[] = $responseMessage->toArray();

            foreach ($responseMessage->toolCalls as $toolCall) {
                $functionName = $toolCall->function->name;
                $arguments = json_decode($toolCall->function->arguments, true) ?? [];

                $result = ['error' => 'Tool not found'];

                if (isset($this->tools[$functionName])) {
                    try {
                        // Automatically inject contextual IDs if missing from tool arguments
                        if (!isset($arguments['doctor_id']) && isset($context['doctor_id'])) {
                            $arguments['doctor_id'] = $context['doctor_id'];
                        }
                        
                        $result = $this->tools[$functionName]::handle($arguments);
                    } catch (\Exception $e) {
                        $result = ['error' => $e->getMessage()];
                    }
                }

                $messages[] = [
                    'role' => 'tool',
                    'tool_call_id' => $toolCall->id,
                    'content' => json_encode($result),
                ];
            }
        }

        return "I'm having trouble retrieving all the required details right now. Please try again.";
    }

    /**
     * Construct the detailed system prompt using dynamic context.
     */
    protected function buildSystemPrompt(array $context): string
    {
        $today = Carbon::now();
        $currentTime = Carbon::now()->format('H:i');

        $patientName = $context['patient_name'] ?? 'Valued Patient';
        $patientPhone = $context['patient_phone'] ?? 'Unknown';
        $doctorName = $context['doctor_name'] ?? 'our clinic doctor';
        $doctorId = $context['doctor_id'] ?? 'Not specified';

        return <<<PROMPT
You are an intelligent, friendly customer support assistant currently assisting patients on behalf of Dr. {$doctorName}.

=== CURRENT SYSTEM CONTEXT ===
- Today's Date & Day: {$today}
- Current Time: {$currentTime}

=== PATIENT CONTEXT ===
- Patient Name: {$patientName}
- Patient Phone: {$patientPhone}

=== DOCTOR CONTEXT ===
- Doctor Name: Dr. {$doctorName}
- Doctor ID: {$doctorId}

=== OPERATIONAL RULES ===
1. Personalization: Greet the patient naturally using their name when appropriate.
2. Tool Usage: NEVER invent or guess available schedule dates, time slots, or appointments. Always call the appropriate tool.
3. Default Parameters: When calling schedule or slot tools, use Doctor ID {$doctorId} unless the patient explicitly asks for a different doctor.
4. Language: Always respond in the same language the user speaks (Arabic or English).
5. Medical Disclaimer: Never offer direct medical diagnoses or emergency prescriptions. Advise emergency patients to visit the clinic directly or contact emergency services.
PROMPT;
    }
}