<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\States\AdminMenu;
use App\Enums\ConversationState;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AdminMenuResponseTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_option_returns_a_single_list_message_with_the_result_as_body()
    {
        $fixtures = $this->makeFixtures();

        AdminMenu::handleResponse($fixtures['conversation'], [
            'type' => 'interactive',
            'value' => 'doctor_today_appointments',
            'from' => '987654321',
        ]);

        Http::assertSentCount(1);

        Http::assertSent(function ($request) {
            $interactive = $request->data()['interactive'] ?? [];
            $rows = $interactive['action']['sections'][0]['rows'] ?? [];
            $ids = array_column($rows, 'id');

            return ($interactive['type'] ?? null) === 'list'
                && ($interactive['body']['text'] ?? '') === 'لا يوجد يوم عمل مسجل لليوم.'
                && ! str_contains($interactive['body']['text'] ?? '', 'اهلا دكتور')
                && in_array('doctor_today_appointments', $ids, true);
        });
    }

    public function test_admin_menu_entry_still_shows_the_welcome_message()
    {
        $fixtures = $this->makeFixtures();

        AdminMenu::execute($fixtures['conversation'], []);

        Http::assertSent(function ($request) {
            $interactive = $request->data()['interactive'] ?? [];

            return ($interactive['type'] ?? null) === 'list'
                && str_contains($interactive['body']['text'] ?? '', 'اهلا دكتور');
        });
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
        Patient::create(['user_id' => $patientUser->id]);

        $conversation = WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => $doctorAccount->id,
            'user_id' => $patientUser->id,
            'phone_number' => '987654321',
            'state' => ConversationState::ADMIN_MENU,
            'data' => ['name' => 'John Doe'],
        ]);

        return [
            'conversation' => $conversation,
            'doctor' => $doctor,
            'doctorAccount' => $doctorAccount,
        ];
    }
}
