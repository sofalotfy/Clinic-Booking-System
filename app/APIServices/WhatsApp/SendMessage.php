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

    public static function list(
    string $phoneNumberId,
    string $accessToken,
    string $to,
    string $text,
    string $buttonText,
    array $rows,
    ?string $header = null,
    string $sectionTitle = 'Options'
): array {

    $payload = [
        'messaging_product' => 'whatsapp',
        'to' => $to,
        'type' => 'interactive',
        'interactive' => [
            'type' => 'list',
            'body' => [
                'text' => $text,
            ],
            'action' => [
                'button' => $buttonText,
                'sections' => [
                    [
                        'title' => $sectionTitle,
                        'rows' => array_map(function ($row) {
                            $item = [
                                'id'    => (string) $row['id'],
                                'title' => substr($row['title'], 0, 24),
                            ];

                            if (!empty($row['description'])) {
                                $item['description'] = substr($row['description'], 0, 72);
                            }

                            return $item;
                        }, $rows),
                    ],
                ],
            ],
        ],
    ];

    if ($header) {
        $payload['interactive']['header'] = [
            'type' => 'text',
            'text' => $header,
        ];
    }

    \Log::info('WhatsApp List Payload', $payload);

    $response = Http::withToken($accessToken)
        ->post(
            "https://graph.facebook.com/v23.0/{$phoneNumberId}/messages",
            $payload
        );

    \Log::info($response->status());
    \Log::info($response->body());

    if ($response->failed()) {
        throw new \Exception($response->body());
    }

    return $response->json();
}

}