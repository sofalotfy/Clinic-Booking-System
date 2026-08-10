<?php

namespace App\APIServices\WhatsApp\AI\Services;

use App\Models\WhatsAppConversation;
use App\Enums\ConversationState;
use App\APIServices\WhatsApp\ExecutionRouter;
use Illuminate\Support\Facades\Log;
use Throwable;

class ExitAiModeTool
{
    /**
     * Define the schema so the AI model knows when and how to call this tool.
     */
    public static function definition(): array
    {
        return [
            'type' => 'function',
            'function' => [
                'name' => 'exit_ai_mode',
                'description' => 'Exit AI chat mode and return the user to the interactive main menu.',
                'parameters' => [
                    'type' => 'object',
                    'properties' => [
                        'patient_id' => [
                            'type' => 'integer',
                            'description' => 'The integer ID of the patient.',
                        ],
                    ],
                    'required' => ['patient_id'],
                ],
            ],
        ];
    }

    /**
     * Execute resetting the conversation state and routing to the main menu.
     */
    public static function handle(array $args): array
    {
        try {
            $conversation = $args['conversation'];
            $message = $args['message'];

            // 2. Update conversation state to MAIN_MENU
            $conversation->update([
                'state' => ConversationState::MAIN_MENU,
                'step'  => null,
            ]);

            // 4. Route execution back to the main router
            ExecutionRouter::execute($conversation, $message);

            return [
                'status'  => 'success',
                'message' => 'Successfully exited AI mode and returned to MAIN_MENU.',
            ];

        } catch (Throwable $e) {
            Log::error('ExitAiModeTool Error: ' . $e->getMessage(), [
                'exception' => $e,
                'args'      => $args,
            ]);

            return [
                'status'  => 'error',
                'message' => 'Failed to exit AI mode: ' . $e->getMessage(),
            ];
        }
    }
}