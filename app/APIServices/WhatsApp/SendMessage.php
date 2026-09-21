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

    public static function location(
        string $phoneNumberId,
        string $accessToken,
        string $to,
        float $latitude,
        float $longitude,
        ?string $name = null,
        ?string $address = null
    ) {
        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v23.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'location',
                'location' => [
                    'latitude' => $latitude,
                    'longitude' => $longitude,
                    'name' => $name,
                    'address' => $address,
                ],
            ]);

        if ($response->failed()) {
            \Log::info('SEND MESSAGE ' . $response->body());
            return;
            throw new \Exception($response->body());
        }

        return $response->json();
    }

    public static function template(
        string $phoneNumberId,
        string $accessToken,
        string $to,
        string $templateName,
        string $languageCode = 'en_US',
        array $bodyParams = [],
        ?array $header = null,
        array $urlButtons = []
    ) {
        $components = [];

        // Header: ['type' => 'image|video|document|text', 'value' => 'url or text']
        if ($header) {
            $type = $header['type'];
            $components[] = [
                'type' => 'header',
                'parameters' => [
                    $type === 'text'
                        ? ['type' => 'text', 'text' => $header['value']]
                        : ['type' => $type, $type => ['link' => $header['value']]],
                ],
            ];
        }

        // Body: ['Ahmed', '#10432'] for {{1}}, {{2}}
        // or ['name' => 'Ahmed'] for named variables like {{name}}
        if (!empty($bodyParams)) {
            $components[] = [
                'type' => 'body',
                'parameters' => collect($bodyParams)->map(function ($value, $key) {
                    $param = ['type' => 'text', 'text' => (string) $value];

                    if (is_string($key)) {
                        $param['parameter_name'] = $key;
                    }

                    return $param;
                })->values()->toArray(),
            ];
        }

        // Dynamic URL buttons: [0 => 'abc123'] (button index => URL suffix)
        foreach ($urlButtons as $index => $suffix) {
            $components[] = [
                'type' => 'button',
                'sub_type' => 'url',
                'index' => (string) $index,
                'parameters' => [
                    ['type' => 'text', 'text' => (string) $suffix],
                ],
            ];
        }

        $template = [
            'name' => $templateName,
            'language' => ['code' => $languageCode],
        ];

        if (!empty($components)) {
            $template['components'] = $components;
        }

        $response = Http::withToken($accessToken)
            ->post("https://graph.facebook.com/v23.0/{$phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to' => $to,
                'type' => 'template',
                'template' => $template,
            ]);

        if ($response->failed()) {
            \Log::info('SEND TEMPLATE ' . $response->body());
            return;
            throw new \Exception($response->body());
        }

        return $response->json();
    }
}