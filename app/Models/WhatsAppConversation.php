<?php

namespace App\Models;

use App\Enums\ConversationState;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WhatsAppConversation extends Model
{
    protected $guarded = [];

    protected $casts = [
        'state' => ConversationState::class,
        'data' => 'array',
        'last_activity_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    public function doctorWhatsAppAccount()
    {
        // Explicit foreign key: Str::snake('doctorWhatsAppAccount') derives
        // 'doctor_whats_app_account_id', which is not the real column, so
        // leaving this implicit made the relation always resolve to null.
        return $this->belongsTo(DoctorWhatsAppAccount::class, 'doctor_whatsapp_account_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function patient(): ?Patient
    {
        return $this->user?->isPatient() ? $this->user->patient : null;
    }

    public function messages()
    {
        return $this->hasMany(WhatsappMessages::class, 'whats_app_conversation_id');
    }
}
