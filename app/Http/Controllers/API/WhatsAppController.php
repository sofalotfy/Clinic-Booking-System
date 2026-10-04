<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Doctor;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ConversationManager;
use Illuminate\Support\Facades\Http;

class WhatsAppController extends Controller
{
    /**
     * Verify webhook with Meta.
     */
    public function verify(Request $request)
    {   
        \Log::info('Meta verification hit', $request->all());

        if (
            $request->get('hub_mode') === 'subscribe' &&
            $request->get('hub_verify_token') === config('services.whatsapp.verify_token')
        ) {
            return response($request->get('hub_challenge'), 200)
                ->header('Content-Type', 'text/plain');
        }

        return response('Forbidden', 403)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Receive webhook events.
     */
    public function receive(Request $request)
    {
        // // Only production forwards, so staging can never loop back
        // if (app()->environment('production') && $this->isForTestNumber($request)) {
        //     return $this->forwardToStaging($request);
        // }
        ConversationManager::execute($request->all());

        return response()->json([
            'success' => true,
        ]);
    }

    private function isForTestNumber(Request $request): bool
    {
        $id = data_get($request->all(), 'entry.0.changes.0.value.metadata.phone_number_id');

        return $id && (string) $id === (string) config('services.whatsapp.test_phone_number_id');
    }

    private function forwardToStaging(Request $request)
    {
        try {
            $url = config('services.whatsapp.staging_webhook_url');
            Log::info('Forwarding webhook to staging', [
                'url' => $url,
                'environment' => app()->environment(),
                'phone_number_id' => data_get($request->all(), 'entry.0.changes.0.value.metadata.phone_number_id'),
            ]);

            $response = Http::timeout(10)
                ->withHeaders([
                    'X-Hub-Signature-256' => $request->header('X-Hub-Signature-256', ''),
                ])
                ->withBody($request->getContent(), 'application/json')
                ->post($url);

            Log::info('Forward to staging completed', [
                'status' => $response->status(),
                'success' => $response->successful(),
            ]);

            return response($response->body(), $response->status())
                ->header('Content-Type', $response->header('Content-Type') ?? 'text/plain');
        } catch (\Throwable $e) {
            Log::warning('Forward to staging failed: ' . $e->getMessage());

            return response('OK', 200); // keep Meta happy
        }
    }
}