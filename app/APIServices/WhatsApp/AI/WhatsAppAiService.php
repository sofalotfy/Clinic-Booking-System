<?php

namespace App\APIServices\WhatsApp\AI;

use App\APIServices\WhatsApp\AI\Services\GetDoctorSlotsTool;
use App\APIServices\WhatsApp\AI\Services\GetDoctorAvailableDays;
use App\APIServices\WhatsApp\AI\Services\GetDoctorDays;
use App\APIServices\WhatsApp\AI\Services\BookAppointmentTool;
use App\APIServices\WhatsApp\AI\Services\CancelAppointmentTool;
use App\APIServices\WhatsApp\AI\Services\ExitAiModeTool;
use OpenAI\Laravel\Facades\OpenAI;
use Carbon\Carbon;

class WhatsAppAiService
{
    protected array $tools = [
        // 'get_available_slots' => GetDoctorSlotsTool::class,
        // 'get_available_days'  => GetDoctorAvailableDays::class,
        // 'get_days_ids'        => GetDoctorDays::class,
        'start_cancellation_flow'   => CancelAppointmentTool::class,
        'start_booking_or_reschedule_flow'    => BookAppointmentTool::class,
        'exit_ai_mode'          => ExitAiModeTool::class,
    ];

    /**
     * Send user message and chat history to LLM and handle tool calls.
     */
    public function ask(string $userMessage, array $history = [], array $context = []): string
    {
        $messages = [
            [
                'role' => 'system',
                'content' => $this->buildSystemPrompt($context),
            ],
        ];

        // Format history correctly (Ensuring array structure)
        foreach ($history as $msg) {
            $messages[] = [
                'role'    => $msg['role'],
                'content' => $msg['content'] ?? '',
            ];
        }

        // Append active user message
        $messages[] = [
            'role'    => 'user',
            'content' => $userMessage,
        ];

        $toolSchemas = array_values(array_map(fn($class) => $class::definition(), $this->tools));

        $maxTurns = 5;
        $turns = 0;

        while ($turns < $maxTurns) {
            $turns++;

            $payload = [
                'model'    => 'llama-3.3-70b-versatile',
                'messages' => $messages,
            ];

            if (!empty($toolSchemas)) {
                $payload['tools'] = $toolSchemas;
                $payload['tool_choice'] = 'auto';
            }

            $response = OpenAI::chat()->create($payload);
            $choice = $response->choices[0];
            $responseMessage = $choice->message;

            // Handshake & Tool Call Resolution
            if (!empty($responseMessage->toolCalls)) {
                // Properly serialize assistant message with tool calls
                $assistantMsg = [
                    'role'       => 'assistant',
                    'content'    => $responseMessage->content ?? null,
                    'tool_calls' => [],
                ];

                foreach ($responseMessage->toolCalls as $toolCall) {
                    $assistantMsg['tool_calls'][] = [
                        'id'       => $toolCall->id,
                        'type'     => 'function',
                        'function' => [
                            'name'      => $toolCall->function->name,
                            'arguments' => $toolCall->function->arguments,
                        ],
                    ];
                }
                $messages[] = $assistantMsg;

                // Execute tools
                foreach ($responseMessage->toolCalls as $toolCall) {
                    $functionName = $toolCall->function->name;
                    $arguments = json_decode($toolCall->function->arguments, true) ?? [];

                    $result = ['error' => 'Tool not found'];

                    if (isset($this->tools[$functionName])) {
                        try {
                            if (!isset($arguments['doctor_id']) && isset($context['doctor_id'])) {
                                $arguments['doctor_id'] = $context['doctor_id'];
                            }
                            if (!isset($arguments['patient_id']) && isset($context['patient_id'])) {
                                $arguments['patient_id'] = $context['patient_id'];
                            }
                            
                            \Log::info("Executing WhatsApp AI Tool: {$functionName}", $arguments);
                            $result = $this->tools[$functionName]::handle($arguments);
                        } catch (\Exception $e) {
                            $result = ['error' => $e->getMessage()];
                        }
                    }

                    $messages[] = [
                        'role'         => 'tool',
                        'tool_call_id' => $toolCall->id,
                        'content'      => json_encode($result),
                    ];
                }

                // Loop back to give tools output to LLM
                continue;
            }

            // Return clean final answer text
            return $this->cleanOutput($responseMessage->content ?? '');
        }

        return "I am currently unable to retrieve the schedule details. Please try again or speak with our receptionist.";
    }

    protected function cleanOutput(string $text): string
    {
        $cleaned = preg_replace('/<function=.*?>.*?<\/function>/s', '', $text);
        $cleaned = preg_replace('/<function>.*?<\/function>/s', '', $cleaned);

        return trim($cleaned);
    }

    protected function buildSystemPrompt(array $context): string
    {
        $now = Carbon::now();
        $dayOfWeek = $now->format('l');      // e.g., Sunday
        $dateFormatted = $now->format('F j, Y'); // e.g., August 9, 2026
        $currentTime = $now->format('g:i A');  // e.g., 11:59 AM

        $patientID    = $context['patient_id'] ?? 'Unknown';
        $patientName  = $context['patient_name'] ?? 'Valued Patient';
        $patientPhone = $context['patient_phone'] ?? 'Unknown';

        $appointmentDate = $context['appointment_date'] ?? null;
        
        $doctorName   = $context['doctor_name'] ?? 'our specialist';
        $doctorId     = $context['doctor_id'] ?? 'Not specified';

        // Build active appointment context block if date exist
        $appointmentContext = '';
        if ($appointmentDate) {
            $appointmentContext = "\n=== ACTIVE APPOINTMENT CONTEXT ===";
            $appointmentContext .= "\n- Selected Date: {$appointmentDate}";
            $appointmentContext .= "\n";
        }
        return <<<PROMPT
You are a helpful, warm customer support assistant for Dr. {$doctorName}'s clinic on WhatsApp.

=== CLINIC TEMPORAL CONTEXT ===
- Day of Week: {$dayOfWeek}
- Today's Date: {$dateFormatted}
- Current Time: {$currentTime}

=== ACTIVE PATIENT CONTEXT ===
- Patient Name: {$patientName}
- Patient Phone: {$patientPhone}
- Patient ID: {$patientID}

=== ASSIGNED DOCTOR CONTEXT ===
- Doctor Name: Dr. {$doctorName}
- Doctor ID: {$doctorId} (integer)

    {$appointmentContext}

=== CRITICAL OPERATIONAL RULES ===
1. LANGUAGE: Respond ONLY in the primary language used by the patient in their last message (Arabic or English).
2. REAL-TIME DATA ONLY: NEVER invent, hallucinate, or estimate schedules, available days, or available time slots. You MUST execute the proper tool to query real-time clinic data.
3. DEFAULT PARAMETERS: Pass `doctor_id`: {$doctorId} as an integer when querying availability tools unless instructed otherwise.
4. WHATSAPP FORMATTING: Keep responses concise and easy to read on mobile devices. Use short paragraphs and bold text (*bold*) where appropriate.
5. SCOPE & SAFETY: Do not offer direct medical diagnoses or emergency triage. For emergency conditions, instruct the patient to proceed directly to the nearest emergency room.
6. FLOWS: do not exit ai mode while use is in a flow (booking, cancelation, etc.) unless explicitly asked to by the user
PROMPT;
    }
}