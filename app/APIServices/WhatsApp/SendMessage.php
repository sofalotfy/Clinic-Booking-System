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
        $response = Http::withToken("EABBbX1ZCpBBsBR48gxwILiGHVe3b5kUHkbll4q62n7O041kJ6JOQneEZCzDwfYuro4OqZACLVViUnadvyfgG4A6OKbIFZCdlSdZCgZCrN5GfBJs7EaELdujoFHFdEgZCsgxmEqXwydAlrZC03FZBBmBE1sjnStxUljnbi1hwhhKqiqbjfAxaHUZCYzirjpEoLKVEM4mFEdCpwaNOAgxt45NcOa0t73WbTc6lDx3TgOB7XBRskLvDTZA5aczvfJ4Al4TovtVJpUv9bok1Uk0ZB15rRCvMqkHMfgZDZD")
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