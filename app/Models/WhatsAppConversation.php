<?php

namespace App\Models;

use App\APIServices\WhatsApp\ExecutionRouter;
use App\Enums\ConversationState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WhatsAppConversation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'state'            => ConversationState::class,
        'data'             => 'array',
        'last_activity_at' => 'datetime',
        'expires_at'       => 'datetime',
    ];

    /*
    |--------------------------------------------------------------------
    | Relationships
    |--------------------------------------------------------------------
    */

    public function doctorWhatsAppAccount(): BelongsTo
    {
        return $this->belongsTo(
            DoctorWhatsAppAccount::class,
            'doctor_whatsapp_account_id'
        );
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(WhatsappMessages::class, 'whats_app_conversation_id');
    }

    public function patient(): ?Patient
    {
        return $this->user?->isPatient() ? $this->user->patient : null;
    }

    /*
    |--------------------------------------------------------------------
    | State machine
    |--------------------------------------------------------------------
    */

    /**
     * Move the conversation to a new state and let the router handle
     * the incoming message under that state.
     */
    public function changeState(ConversationState $state, $message)
    {
        $this->update([
            'state' => $state,
            'step'  => null,
        ]);

        return ExecutionRouter::execute($this, $message);
    }

    /**
     * Enter a sub-flow: remember the current state on the call stack,
     * then switch to the new state.
     */
    public function startFlow(ConversationState $state, $message)
    {
        $this->pushCallStack($this->state);

        return $this->changeState($state, $message);
    }

    /**
     * Leave the current sub-flow: pop the previous state off the call
     * stack and return to it.
     */
    public function endFlow($message)
    {
        $state = $this->popCallStack();

        return $this->changeState($state, $message);
    }

    /**
     * End the conversation entirely.
     */
    public function end(): void
    {
        $this->delete();
    }

    /*
    |--------------------------------------------------------------------
    | Call stack (used by startFlow / endFlow)
    |--------------------------------------------------------------------
    */

    public function pushCallStack(ConversationState $state): void
    {
        $data = $this->data ?? [];
        $data['callStack'][] = $state;

        $this->update([
            'data' => $data,
        ]);
    }

    public function popCallStack(): ConversationState
    {
        $data  = $this->data ?? [];
        $state = array_pop($data['callStack'] ?? []) ?? ConversationState::START;

        $this->update([
            'data' => $data,
        ]);

        return $state;
    }

    /*
    |--------------------------------------------------------------------
    | Conversation data
    |--------------------------------------------------------------------
    */

    /**
     * Merge new key/value pairs into the conversation's stored data.
     * Existing keys are overwritten by the new values.
     */
    public function pushData(array $data): void
    {
        $this->update([
            'data' => array_merge($this->data ?? [], $data),
        ]);
    }
}