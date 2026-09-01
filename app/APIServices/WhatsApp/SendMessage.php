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
    )
    {
        \Log::info('SEND MESSAGE ' . $accessToken);
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
            \Log::info('SEND MESSAGE ' . $response->body());
            return;
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
    ) {
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
            \Log::info('SEND MESSAGE ' . $response->body());
            return;
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    public static function list(
        string $phoneNumberId,
        string $accessToken,
        string $to,
        string $text,
        string $buttonText,
        array $rows,
        string $title = 'Select an option',
        string $sectionTitle = 'Options'
    ) {
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v23.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'interactive',
                'interactive' => [
                    'type' => 'list',
                    'header' => [
                        'type' => 'text',
                        'text' => $title,
                    ],
                    'body' => [
                        'text' => $text,
                    ],
                    'action' => [
                        'button' => $buttonText,
                        'sections' => [
                            [
                                'title' => $sectionTitle,
                                'rows' => collect($rows)->map(function ($row) {
                                    return [
                                        'id' => $row['id'],
                                        'title' => $row['title'],
                                        'description' => $row['description'] ?? '',
                                    ];
                                })->toArray(),
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            \Log::info('SEND MESSAGE ' . $response->body());
            return;
            throw new \Exception($response->body());
        }

        return $response->json();
    }

}