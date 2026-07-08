<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppController extends Controller
{
    /**
     * Verify webhook with Meta.
     */
    public function verify(Request $request)
{
    dd([
        'token_from_meta' => $request->query('hub.verify_token'),
        'token_from_env' => env('WHATSAPP_VERIFY_TOKEN'),
        'token_from_config' => config('services.whatsapp.verify_token'),
    ]);
}

    /**
     * Receive webhook events.
     */
    public function receive(Request $request)
    {
        // Log everything while developing
        Log::info('WhatsApp Webhook', $request->all());

        $entry = $request->input('entry.0.changes.0.value');

        if (!$entry) {
            return response()->json(['success' => true]);
        }

        // Incoming messages
        if (isset($entry['messages'])) {

            foreach ($entry['messages'] as $message) {

                $from = $message['from'];
                $type = $message['type'];

                switch ($type) {

                    case 'text':
                        $text = $message['text']['body'];

                        Log::info('Incoming Text', [
                            'from' => $from,
                            'text' => $text,
                        ]);

                        // TODO:
                        // WhatsAppService::reply($from, "Hello!");

                        break;

                    case 'interactive':
                        Log::info('Interactive Reply', $message);
                        break;

                    case 'button':
                        Log::info('Button Reply', $message);
                        break;

                    default:
                        Log::info('Unhandled Message Type', [
                            'type' => $type,
                        ]);
                }
            }
        }

        // Delivery / Read receipts
        if (isset($entry['statuses'])) {

            foreach ($entry['statuses'] as $status) {

                Log::info('Message Status', [
                    'id' => $status['id'],
                    'status' => $status['status'],
                    'recipient' => $status['recipient_id'],
                ]);
            }
        }

        return response()->json([
            'success' => true,
        ]);
    }
}