<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\ConversationManager;
use App\Enums\ConversationState;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppBlockedPatientTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_blocked_patient_is_told_and_no_conversation_starts()
    {
        $fixtures = $this->makeFixtures();

        PatientBlock::create([
            'doctor_id' => $fixtures['doctor']->id,
            'patient_id' => $fixtures['patient']->id,
            'blocked_by' => $fixtures['doctorUser']->id,
            'blocked_at' => now(),
        ]);

        $this->webhook('966500000000', 'wamid.blocked');

        Http::assertSent(function ($request) {
            return $request['to'] === '966500000000'
                && str_contains($request['text']['body'] ?? '', 'تم حظر');
        });

        $this->assertSame(0, WhatsAppConversation::count());
    }

    public function test_unblocked_patient_reaches_the_main_menu()
    {
        $this->makeFixtures();

        $this->webhook('966500000000', 'wamid.allowed');

        $conversation = WhatsAppConversation::first();

        $this->assertNotNull($conversation);
        $this->assertSame(ConversationState::MAIN_MENU, $conversation->state);
    }

    private function webhook(string $from, string $messageId): void
    {
        ConversationManager::execute([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'metadata' => ['phone_number_id' => '123456789'],
                        'messages' => [[
                            'from' => $from,
                            'id' => $messageId,
                            'type' => 'text',
                            'text' => ['body' => 'hi'],
                        ]],
                    ],
                ]],
            ]],
        ]);
    }

    private function makeFixtures(): array
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
            'notifications_phone' => null,
        ]);

        DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
        ]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'email' => 'patient@example.com',
            'phone' => '966500000000',
            'password' => bcrypt('password'),
        ]);
        $patient = Patient::create(['user_id' => $patientUser->id]);

        return compact('doctorUser', 'doctor', 'patientUser', 'patient');
    }
}
