<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\States\Start;
use App\Enums\ConversationState;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppNewConversationRoutingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_clinic_notifications_phone_routes_to_admin_menu()
    {
        // Clinic number stored with formatting; WhatsApp sends plain digits.
        $fixtures = $this->makeFixtures('+966 51 234 5678', '966512345678');

        Start::execute($fixtures['conversation'], [
            'type' => 'text',
            'value' => 'hi',
            'from' => '966512345678',
        ]);

        $this->assertSame(
            ConversationState::ADMIN_MENU,
            $fixtures['conversation']->fresh()->state
        );
    }

    public function test_other_phone_routes_to_patient_main_menu()
    {
        $fixtures = $this->makeFixtures('+966 51 234 5678', '966500000000');

        Start::execute($fixtures['conversation'], [
            'type' => 'text',
            'value' => 'hi',
            'from' => '966500000000',
        ]);

        $this->assertSame(
            ConversationState::MAIN_MENU,
            $fixtures['conversation']->fresh()->state
        );
    }

    public function test_clinic_without_notifications_phone_routes_to_patient_main_menu()
    {
        $fixtures = $this->makeFixtures(null, '966512345678');

        Start::execute($fixtures['conversation'], [
            'type' => 'text',
            'value' => 'hi',
            'from' => '966512345678',
        ]);

        $this->assertSame(
            ConversationState::MAIN_MENU,
            $fixtures['conversation']->fresh()->state
        );
    }

    private function makeFixtures(?string $notificationsPhone, string $conversationPhone): array
    {
        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'email' => 'doctor@example.com',
            'phone' => '1111111111',
            'password' => bcrypt('password'),
        ]);
        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        Clinic::create([
            'doctor_id' => $doctor->id,
            'notifications_phone' => $notificationsPhone,
        ]);

        $doctorAccount = DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
        ]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'email' => 'patient@example.com',
            'phone' => $conversationPhone,
            'password' => bcrypt('password'),
        ]);
        Patient::create(['user_id' => $patientUser->id]);

        $conversation = WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => $doctorAccount->id,
            'user_id' => $patientUser->id,
            'phone_number' => $conversationPhone,
            'state' => ConversationState::START,
            'data' => ['name' => 'John Doe'],
        ]);

        return [
            'conversation' => $conversation,
            'doctor' => $doctor,
            'doctorAccount' => $doctorAccount,
        ];
    }
}
