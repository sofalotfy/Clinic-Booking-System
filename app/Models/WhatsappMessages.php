<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappMessages extends Model
{
    protected $guarded = [];

    public function conversation()
    {
        return $this->belongsTo(WhatsAppConversation::class, 'whats_app_conversation_id');
    }

    public static function getHistory(int $conversationId, int $limit = 10): array
    {
        return self::where('whats_app_conversation_id', $conversationId)
            ->orderBy('created_at', 'asc')
            ->take($limit)
            ->get()
            ->map(fn($msg) => [
                'role' => $msg->role,
                'content' => $msg->content,
            ])
            ->toArray();
    }
}
