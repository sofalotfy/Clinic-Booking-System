<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Enums\ConversationState;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;

class WhatsAppConversation extends Model
{
    protected $fillable = [
        'doctor_whatsapp_account_id',
        'patient_id',
        'phone_number',
        'state',
        'step',
        'data',
        'last_activity_at',
        'expires_at',
    ];

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

    public function patient()
    {
        return $this->belongsTo(Patient::class);
    }
}
