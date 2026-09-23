<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\States\BookAppointment;
use App\APIServices\WhatsApp\States\InfoConfirmation;
use App\Enums\ConversationState;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppInvalidResponseTest extends TestCase
{
    use RefreshDatabase;

    private const INVALID_RESPONSE_MESSAGE = 'هذا الرد غير صالح، فضلا اختر أحد الخيارات المتاحة';

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_unknown_interactive_value_triggers_invalid_response_fallback()
    {
        $fixtures = $this->makeFixtures(ConversationState::INFO_CONFIRMATION);

        InfoConfirmation::handleResponse($fixtures['conversation'], [
            'type' => 'interactive',
            'value' => 'bogus_option',
            'from' => '987654321',
        ]);

        Http::assertSent(function ($request) {
            return str_contains(
                $request->data()['text']['body'] ?? '',
                self::INVALID_RESPONSE_MESSAGE
            );
        });
    }

    public function test_wrong_message_type_triggers_invalid_response_fallback()
    {
        $fixtures = $this->makeFixtures(ConversationState::INFO_CONFIRMATION);

        InfoConfirmation::handleResponse($fixtures['conversation'], [
            'type' => 'text',
            'value' => 'hello',
            'from' => '987654321',
        ]);

        Http::assertSent(function ($request) {
            return str_contains(
                $request->data()['text']['body'] ?? '',
                self::INVALID_RESPONSE_MESSAGE
            );
        });
    }

    public function test_book_appointment_unknown_day_id_triggers_invalid_response_fallback()
    {
        $fixtures = $this->makeFixtures(ConversationState::BOOK_APPOINTMENT);

        BookAppointment::handleResponse($fixtures['conversation'], [
            'type' => 'interactive',
            'value' => '999999',
            'from' => '987654321',
        ]);

        Http::assertSent(function ($request) {
            return str_contains(
                $request->data()['text']['body'] ?? '',
                self::INVALID_RESPONSE_MESSAGE
            );
        });
    }

    private function makeFixtures(ConversationState $state): array
    {
        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'email' => 'doctor@example.com',
            'phone' => '1111111111',
            'password' => bcrypt('password'),
        ]);
        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        $doctorAccount = DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
        ]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'email' => 'patient@example.com',
            'phone' => '2222222222',
            'password' => bcrypt('password'),
        ]);
        $patient = Patient::create(['user_id' => $patientUser->id]);

        $conversation = WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => $doctorAccount->id,
            'user_id' => $patientUser->id,
            'phone_number' => '987654321',
            'state' => $state,
            'data' => [
                'name' => 'John Doe',
                'age' => 30,
                'address' => 'Test Address',
                'callStack' => [ConversationState::START->value],
            ],
        ]);

        return [
            'conversation' => $conversation,
            'doctor' => $doctor,
            'doctorAccount' => $doctorAccount,
            'patient' => $patient,
            'patientUser' => $patientUser,
        ];
    }
}
