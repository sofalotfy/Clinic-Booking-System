<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ConversationState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\User;

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
        return $this->belongsTo(DoctorWhatsAppAccount::class);
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
