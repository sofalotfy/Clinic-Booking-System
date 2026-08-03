<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\States\InfoConfirmation;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Models\WhatsAppConversation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class InfoConfirmationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_confirm_response_with_no_name_change()
    {
        // 1. Create a doctor user and doctor profile
        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
        ]);
        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        // 2. Create a doctor WhatsApp account
        $doctorAccount = DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
        ]);

        // 3. Create a patient user and patient profile
        $patientUser = User::create([
            'name' => 'John Doe',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
        ]);
        $patient = Patient::create(['user_id' => $patientUser->id]);

        // 4. Create a WhatsApp conversation
        $conversation = WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => $doctorAccount->id,
            'patient_id' => $patient->id,
            'phone_number' => '987654321',
            'state' => \App\Enums\ConversationState::INFO_CONFIRMATION,
            'data' => [
                'name' => 'John Doe', // Same name
                'age' => 30,
                'address' => 'Test Address',
                'callStack' => [\App\Enums\ConversationState::START->value],
            ],
        ]);

        $message = [
            'type' => 'interactive',
            'value' => 'confirm',
            'from' => '987654321',
        ];

        // 5. Run the response handler
        InfoConfirmation::handleResponse($conversation, $message);

        // 6. Assertions
        $patientUser->refresh();
        $this->assertEquals('John Doe', $patientUser->name);
        $this->assertEquals(30, $patientUser->age);
        $this->assertEquals('Test Address', $patientUser->area);

        // No notification should be created
        $this->assertEquals(0, Notification::count());
    }

    public function test_confirm_response_with_name_change_creates_notification()
    {
        // 1. Create a doctor user and doctor profile
        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
        ]);
        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        // 2. Create a doctor WhatsApp account
        $doctorAccount = DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
        ]);

        // 3. Create a patient user and patient profile
        $patientUser = User::create([
            'name' => 'John Doe',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
        ]);
        $patient = Patient::create(['user_id' => $patientUser->id]);

        // 4. Create a WhatsApp conversation with a different name in the state data
        $conversation = WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => $doctorAccount->id,
            'patient_id' => $patient->id,
            'phone_number' => '987654321',
            'state' => \App\Enums\ConversationState::INFO_CONFIRMATION,
            'data' => [
                'name' => 'Jonathan Doe', // Changed name
                'age' => 31,
                'address' => 'New Address',
                'callStack' => [\App\Enums\ConversationState::START->value],
            ],
        ]);

        $message = [
            'type' => 'interactive',
            'value' => 'confirm',
            'from' => '987654321',
        ];

        // 5. Run the response handler
        InfoConfirmation::handleResponse($conversation, $message);

        // 6. Assertions
        $patientUser->refresh();
        $this->assertEquals('Jonathan Doe', $patientUser->name);
        $this->assertEquals(31, $patientUser->age);
        $this->assertEquals('New Address', $patientUser->area);

        // A notification should be created for the doctor
        $this->assertEquals(1, Notification::count());
        $notification = Notification::first();
        $this->assertEquals($doctorUser->id, $notification->user_id);
        $this->assertEquals(\App\Enums\NotificationsType::PATIENT_PROFILE, $notification->type);
        $this->assertEquals('Patient name changed', $notification->title);
        $this->assertEquals('Patient John Doe has been renamed to Jonathan Doe', $notification->text);
    }
}
