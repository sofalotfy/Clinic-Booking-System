<?php

namespace App\APIServices\WhatsApp;

use Illuminate\Support\Facades\Http;

class SendMessage
{
    public static function execute(
        string $phoneNumberId,
        string $accessToken,
        string $to,
        string $message
    ): array
    {
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v23.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'text',
                'text' => [
                    'body' => $message,
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}