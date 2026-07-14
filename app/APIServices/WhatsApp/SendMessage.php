<?php

namespace App\APIServices\WhatsApp;

use Illuminate\Support\Facades\Http;

class SendMessage
{
    public static function text(
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

    
    public static function buttons(
        string $phoneNumberId,
        string $accessToken,
        string $to,
        string $text,
        array $buttons
    ): array {
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v23.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'button',
                    'body' => [
                        'text' => $text,
                    ],
                    'action' => [
                        'buttons' => collect($buttons)->map(function ($button) {
                            return [
                                'type' => 'reply',
                                'reply' => [
                                    'id' => $button['id'],
                                    'title' => $button['title'],
                                ],
                            ];
                        })->toArray(),
                    ],
                ],
            ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response->json();
    }

}