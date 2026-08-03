<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Doctor;
use App\APIServices\WhatsApp\SendMessage;
use App\Models\DoctorWhatsAppAccount;
use App\APIServices\WhatsApp\ConversationManager;

class WhatsAppController extends Controller
{
    /**
     * Verify webhook with Meta.
     */
    public function verify(Request $request)
    {
        \Log::info('Meta verification hit', $request->alls());

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
        ConversationManager::execute($request->all());

        return response()->json([
            'success' => true,
        ]);
    }
}