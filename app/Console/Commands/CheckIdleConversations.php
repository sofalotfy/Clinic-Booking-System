<?php

namespace App\Console\Commands;

use App\APIServices\WhatsApp\States\IdleCheckState;
use App\Enums\ConversationState;
use App\Models\WhatsAppConversation;
use Illuminate\Console\Command;
use Throwable;

class CheckIdleConversations extends Command
{
    protected $signature = 'app:check-idle-conversations
                            {--minutes=10 : Idle window in minutes, defaults to 10}';

    protected $description = 'Prompt WhatsApp conversations abandoned mid-flow, asking whether to end the chat.';

    private const CHUNK = 500;

    public function handle(): int
    {
        $minutes = filter_var($this->option('minutes'), FILTER_VALIDATE_INT);

        if ($minutes === false || $minutes < 0) {
            $this->error("Invalid minutes [{$this->option('minutes')}], expected a non-negative integer.");

            return self::FAILURE;
        }

        $examined = 0;
        $prompted = 0;
        $failed = 0;

        $query = WhatsAppConversation::query()
            ->with(['user', 'doctorWhatsAppAccount'])
            ->whereIn('state', ConversationState::active())
            ->where('last_activity_at', '<=', now()->subMinutes($minutes));

        $query->chunkById(self::CHUNK, function ($conversations) use (&$examined, &$prompted, &$failed) {
            foreach ($conversations as $conversation) {
                $examined++;

                try {
                    if (! $conversation->doctorWhatsAppAccount) {
                        throw new \RuntimeException(
                            'no WhatsApp account attached to conversation '.$conversation->id
                        );
                    }

                    // Address IdleCheckState directly: routing through ExecutionRouter would
                    // re-run the conversation's current flow handler instead of sending the prompt.
                    $sent = IdleCheckState::execute($conversation);

                    if ($sent === false) {
                        $failed++;

                        continue;
                    }

                    $prompted++;
                } catch (Throwable $exception) {
                    \Log::error(
                        'Idle prompt for conversation '.$conversation->id.' failed: '.$exception->getMessage()
                    );

                    $failed++;
                }
            }
        });

        $skipped = $examined - $prompted - $failed;

        $this->info(
            "Idle check: {$prompted} prompted, {$skipped} skipped, {$failed} failed (window {$minutes}m)"
        );

        // FR-023: a run must report failure when any conversation failed, so that a
        // partial outage is visible to monitoring. Per-conversation failures above are
        // still isolated, so the sweep always completes before this verdict.
        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
