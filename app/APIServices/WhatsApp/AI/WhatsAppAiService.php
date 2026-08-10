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
use Illuminate\Support\Facades\Log;

class WhatsAppAiService
{
    protected array $tools = [
        'start_cancellation_flow'         => CancelAppointmentTool::class,
        'start_booking_or_reschedule_flow' => BookAppointmentTool::class,
        'exit_ai_mode'                    => ExitAiModeTool::class,
    ];

    /**
     * Send user message and chat history to LLM and handle tool calls.
     */
    public function ask(string $userMessage, array $history = [], array $context = []): string
    {
        $messages = [
            [
                'role'    => 'system',
                'content' => $this->buildSystemPrompt($context),
            ],
        ];

        // Format history correctly
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

                $flowTransferred = false;

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
                            
                            Log::info("Executing WhatsApp AI Tool: {$functionName}", $arguments);
                            $result = $this->tools[$functionName]::handle($arguments);

                            // Check if state transition flow took over WhatsApp execution
                            if (
                                in_array($functionName, ['start_cancellation_flow', 'start_booking_or_reschedule_flow', 'exit_ai_mode']) 
                                && ($result['status'] ?? '') === 'success'
                            ) {
                                $flowTransferred = true;
                            }
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

                // If execution was handed over to interactive routing (e.g. state updated), stop AI text generation
                if ($flowTransferred) {
                    return '';
                }

                continue;
            }

            return $this->cleanOutput($responseMessage->content ?? '');
        }

        return "I am currently unable to process your request. Please try again or speak with our receptionist.";
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
        $dayOfWeek = $now->format('l');
        $dateFormatted = $now->format('F j, Y');
        $currentTime = $now->format('g:i A');

        $patientID    = $context['patient_id'] ?? 'Unknown';
        $patientName  = $context['patient_name'] ?? 'Valued Patient';
        $patientPhone = $context['patient_phone'] ?? 'Unknown';

        $appointmentDate = $context['appointment_date'] ?? null;
        $appointmentTime = $context['appointment_time'] ?? null;
        
        $doctorName   = $context['doctor_name'] ?? 'our specialist';
        $doctorId     = $context['doctor_id'] ?? 'Not specified';

        if ($appointmentDate) {
            $appointmentDetails = "- Selected Date: {$appointmentDate}";
            if ($appointmentTime) {
                $appointmentDetails .= "\n- Selected Time: {$appointmentTime}";
            }
            $appointmentContext = "=== ACTIVE APPOINTMENT CONTEXT ===\n" . $appointmentDetails;
        } else {
            $appointmentContext = "=== ACTIVE APPOINTMENT CONTEXT ===\n- Patient currently has no active booked appointment.";
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
3. REAL-TIME DATA ONLY: NEVER invent, hallucinate, or estimate schedules, available days, or time slots.
4. AUTOMATIC PARAMETERS: Always pass `doctor_id`: {$doctorId} and `patient_id`: {$patientID} when triggering tools.
6. SAFETY & SCOPE: Do not provide medical diagnoses or emergency triage. For medical emergencies, instruct the patient to contact emergency services or proceed to the nearest emergency room immediately.
PROMPT;
    }
}