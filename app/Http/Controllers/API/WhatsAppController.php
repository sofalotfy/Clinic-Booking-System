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
        return response()->json([
    'success' => true,
]);
        // Log everything while developing
        \Log::info('WhatsApp Webhook', $request->all());

        // $entry = $request->input('entry.0.changes.0.value');

        // if (!$entry) {
        //     return response()->json(['success' => true]);
        // }

        // // Incoming messages
        // if (isset($entry['messages'])) {

        //     foreach ($entry['messages'] as $message) {

        //         $from = $message['from'];
        //         $type = $message['type'];

        //         switch ($type) {

        //             case 'text':
        //                 $text = $message['text']['body'];

        //                 Log::info('Incoming Text', [
        //                     'from' => $from,
        //                     'text' => $text,
        //                 ]);

        //                 $phoneNumberId = $entry['metadata']['phone_number_id'];

        //                 $whatsappAccount = DoctorWhatsAppAccount::where('phone_number_id', $phoneNumberId)
        //                     ->where('is_active', true)
        //                     ->first();

        //                 if (! $whatsappAccount) {
        //                     Log::warning('No doctor found for WhatsApp account.', [
        //                         'phone_number_id' => $phoneNumberId,
        //                     ]);

        //                     return response()->json(['success' => true]);
        //                 }

        //                 $doctor = $whatsappAccount->doctor;

        //                 SendMessage::execute(
        //                     $whatsappAccount->phone_number_id,
        //                     $whatsappAccount->access_token,
        //                     $from,
        //                     $text
        //                 );

        //                 break;

        //             case 'interactive':
        //                 Log::info('Interactive Reply', $message);
        //                 break;

        //             case 'button':
        //                 Log::info('Button Reply', $message);
        //                 break;

        //             default:
        //                 Log::info('Unhandled Message Type', [
        //                     'type' => $type,
        //                 ]);
        //         }
        //     }
        // }

        // // Delivery / Read receipts
        // if (isset($entry['statuses'])) {

        //     foreach ($entry['statuses'] as $status) {

        //         Log::info('Message Status', [
        //             'id' => $status['id'],
        //             'status' => $status['status'],
        //             'recipient' => $status['recipient_id'],
        //         ]);
        //     }
        // }

        ConversationManager::execute($request->all());

        return response()->json([
            'success' => true,
        ]);
    }
}