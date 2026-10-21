<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\ConversationManager;
use App\APIServices\WhatsApp\States\Notifications\ConsultationReminder;
use App\Enums\AppointmentStatus;
use App\Enums\ConversationState;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Medicine;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Models\Prescription;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Services\Doctors\SuspendDoctor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class ConsultationReminderTest extends TestCase
{
    use RefreshDatabase;

    private const TODAY = '2026-11-01';

    private Doctor $doctor;

    private DoctorWhatsAppAccount $account;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        Http::fake(['graph.facebook.com/*' => Http::response(['success' => true])]);
        $this->travelTo(self::TODAY.' 09:00:00');

        $this->doctor = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Dr. Ahmed', '201000000001')->id]);
        $this->account = DoctorWhatsAppAccount::create([
            'doctor_id' => $this->doctor->id,
            'phone_number_id' => 'pnid-1',
            'access_token' => 'token',
            'is_active' => true,
        ]);
        $this->patient = Patient::create(['user_id' => $this->user(UserType::PATIENT, 'Mona', '201000000002')->id]);
    }

    public function test_week_and_day_reminders_send_the_template_once_each(): void
    {
        $prescription = $this->prescription('2026-11-08');

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('1 sent')->assertSuccessful();

        $this->assertTemplateSent('consultation_week_reminder');
        $this->assertNotNull($prescription->fresh()->week_reminder_sent_at);
        $this->assertSame(ConversationState::CONSULTATION_REMINDER, $this->conversation()->state);

        // A second run the same day sends nothing new
        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('0 sent')->assertSuccessful();
        Http::assertSentCount(1);

        $this->artisan('app:send-consultation-reminders', ['--at' => '2026-11-07'])->expectsOutputToContain('1 sent');
        $this->assertTemplateSent('consultation_day_reminder');
        $this->assertNotNull($prescription->fresh()->day_reminder_sent_at);
    }

    public function test_reminders_are_skipped_when_not_needed(): void
    {
        $booked = $this->prescription('2026-11-08');
        Appointment::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $this->patient->id,
            'date' => '2026-11-08 10:00:00',
            'duration' => 30,
            'status' => AppointmentStatus::ACTIVE,
        ]);

        $blockedPatient = Patient::create(['user_id' => $this->user(UserType::PATIENT, 'Blocked', '201000000003')->id]);
        $this->prescription('2026-11-08', $blockedPatient);
        PatientBlock::create(['doctor_id' => $this->doctor->id, 'patient_id' => $blockedPatient->id, 'blocked_at' => now()]);

        $otherDate = $this->prescription('2026-11-05');

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('0 sent, 2 skipped');
        Http::assertNothingSent();
        $this->assertNull($booked->fresh()->week_reminder_sent_at);
        $this->assertNull($otherDate->fresh()->week_reminder_sent_at);
    }

    public function test_any_upcoming_appointment_with_the_doctor_blocks_both_reminders(): void
    {
        $prescription = $this->prescription('2026-11-08');

        // Booked before the consultation day: by the day reminder it is in
        // the past, but they have been back to the doctor all the same
        $this->appointment('2026-11-03 12:00:00', AppointmentStatus::PENDING);

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('0 sent, 1 skipped');
        $this->artisan('app:send-consultation-reminders', ['--at' => '2026-11-07'])->expectsOutputToContain('0 sent, 1 skipped');

        Http::assertNothingSent();
        $this->assertNull($prescription->fresh()->week_reminder_sent_at);
        $this->assertNull($prescription->fresh()->day_reminder_sent_at);
    }

    public function test_a_follow_up_already_attended_blocks_the_reminder(): void
    {
        $this->prescription('2026-11-08');
        $this->appointment('2026-10-30 10:00:00', AppointmentStatus::DONE);

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('0 sent, 1 skipped');
    }

    public function test_an_appointment_later_today_blocks_the_reminder(): void
    {
        $this->prescription('2026-11-08');
        $this->appointment(self::TODAY.' 18:00:00', AppointmentStatus::ACTIVE);

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('0 sent, 1 skipped');
    }

    public function test_past_cancelled_and_own_appointments_do_not_block_the_reminder(): void
    {
        // The prescription was written today during an appointment nobody marked done yet
        $own = $this->appointment(self::TODAY.' 08:00:00', AppointmentStatus::ACTIVE);
        $this->prescription('2026-11-08', appointment: $own);

        $this->appointment('2026-11-05 10:00:00', AppointmentStatus::CANCELLED);
        // Before the prescription, so not a follow-up
        $this->appointment('2026-10-20 10:00:00', AppointmentStatus::DONE);

        // Another clinic's booking is not this doctor's consultation
        $otherDoctor = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Dr. Other', '201000000009')->id]);
        Appointment::create(['doctor_id' => $otherDoctor->id, 'patient_id' => $this->patient->id, 'date' => '2026-11-06 10:00:00', 'duration' => 30, 'status' => AppointmentStatus::ACTIVE]);

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('1 sent, 0 skipped');
    }

    public function test_suspended_doctors_send_no_reminders(): void
    {
        $this->prescription('2026-11-08');
        SuspendDoctor::execute($this->doctor);

        $this->artisan('app:send-consultation-reminders')->expectsOutputToContain('0 sent, 0 skipped');
        Http::assertNothingSent();
    }

    public function test_moving_the_consultation_date_rearms_the_reminders(): void
    {
        $prescription = $this->prescription('2026-11-08');
        $prescription->update(['week_reminder_sent_at' => now(), 'day_reminder_sent_at' => now()]);

        $prescription->update(['consultation_date' => '2026-11-20']);

        $this->assertNull($prescription->fresh()->week_reminder_sent_at);
        $this->assertNull($prescription->fresh()->day_reminder_sent_at);
    }

    public function test_later_restores_the_state_the_conversation_was_in(): void
    {
        $conversation = $this->existingConversation(ConversationState::MANAGE_APPOINTMENT, ['callStack' => ['main_menu'], 'name' => 'Mona']);
        $this->prescription('2026-11-08');

        $this->artisan('app:send-consultation-reminders');

        $conversation->refresh();
        $this->assertSame(ConversationState::CONSULTATION_REMINDER, $conversation->state);
        $this->assertSame(['manage_appointment', 'main_menu'], $conversation->data['callStack']);

        $this->tapButton(ConsultationReminder::LATER);

        $conversation->refresh();
        $this->assertSame(ConversationState::MANAGE_APPOINTMENT, $conversation->state);
        $this->assertSame(['main_menu'], $conversation->data['callStack']);
        $this->assertArrayNotHasKey('notification_id', $conversation->data);
        $this->assertTrue(Notification::sole()->viewed);
        Http::assertSent(fn (Request $request) => str_contains($request['text']['body'] ?? '', 'في أي وقت'));
    }

    public function test_a_second_reminder_does_not_push_the_reminder_state_itself(): void
    {
        $conversation = $this->existingConversation(ConversationState::MANAGE_APPOINTMENT);
        $this->prescription('2026-11-08');

        $this->artisan('app:send-consultation-reminders');
        $this->artisan('app:send-consultation-reminders', ['--at' => '2026-11-07']);

        $this->assertSame(['manage_appointment'], $conversation->fresh()->data['callStack']);

        $this->tapButton(ConsultationReminder::LATER);

        $this->assertSame(ConversationState::MANAGE_APPOINTMENT, $conversation->fresh()->state);
        $this->assertArrayNotHasKey('callStack', $conversation->fresh()->data);
    }

    public function test_book_button_starts_the_booking_flow(): void
    {
        $conversation = $this->existingConversation(ConversationState::MANAGE_APPOINTMENT);
        $this->prescription('2026-11-08');
        $this->artisan('app:send-consultation-reminders');

        $this->tapButton(ConsultationReminder::BOOK_APPOINTMENT);

        $conversation->refresh();
        $this->assertSame(ConversationState::BOOK_APPOINTMENT, $conversation->state);
        $this->assertArrayNotHasKey('callStack', $conversation->data);
    }

    public function test_view_prescription_button_shows_it_with_the_main_menu_button(): void
    {
        $this->prescription('2026-11-08', medicine: 'Augmentin 1g');
        $this->artisan('app:send-consultation-reminders');

        $this->tapButton(ConsultationReminder::VIEW_PRESCRIPTION);

        $this->assertSame(ConversationState::MAIN_MENU, $this->conversation()->state);
        Http::assertSent(fn (Request $request) => str_contains($request['interactive']['body']['text'] ?? '', 'Augmentin 1g')
            && ($request['interactive']['action']['buttons'][0]['reply']['id'] ?? null) === 'back_to_mainmenu');
    }

    public function test_typing_instead_of_tapping_hands_the_message_back(): void
    {
        $this->prescription('2026-11-08');
        $this->artisan('app:send-consultation-reminders');

        // No conversation before the reminder, so the patient lands in the main menu
        $this->webhook(['type' => 'text', 'text' => ['body' => 'hello']]);

        $this->assertSame(ConversationState::MAIN_MENU, $this->conversation()->state);
    }

    public function test_main_menu_option_shows_the_latest_prescription(): void
    {
        $this->prescription('2026-11-08', medicine: 'Old Medicine');
        $this->prescription(null, medicine: 'Panadol Extra');
        $this->existingConversation(ConversationState::MAIN_MENU);

        $this->webhook(['type' => 'interactive', 'interactive' => ['type' => 'list_reply', 'list_reply' => ['id' => 'view_prescription']]]);

        Http::assertSent(fn (Request $request) => str_contains($request['interactive']['body']['text'] ?? '', 'Panadol Extra'));
        Http::assertNotSent(fn (Request $request) => str_contains(json_encode($request->data(), JSON_UNESCAPED_UNICODE), 'Old Medicine'));
    }

    public function test_prescription_message_is_labelled_and_right_to_left(): void
    {
        $prescription = $this->prescription('2026-11-08', medicine: 'Augmentin 1g');
        $prescription->update(['diagnosis' => 'التهاب اللوزتين', 'notes' => 'سوائل كثيرة']);
        $prescription->medicines()->updateExistingPivot(Medicine::first()->id, ['frequency' => 'كل 12 ساعة', 'instructions' => 'بعد الاكل']);
        $prescription->labs()->attach(\App\Models\Lab::create(['name' => 'CBC', 'name_ar' => 'صورة دم', 'fasting_required' => true])->id);

        $message = \App\APIServices\WhatsApp\Services\FormatPrescriptionToMessage::execute($prescription->fresh());

        foreach (['الروشتة', 'التشخيص', 'الأدوية', '١- *Augmentin 1g*', 'الجرعة: 1 tablet', 'التكرار: كل 12 ساعة', 'تعليمات: بعد الاكل', 'التحاليل المطلوبة', 'يلزم الصيام', 'ملاحظات', 'موعد الاستشارة'] as $expected) {
            $this->assertStringContainsString($expected, $message);
        }

        // Every non-empty line is forced right to left, so English names do not scramble the layout
        foreach (explode("\n", $message) as $line) {
            $this->assertTrue($line === '' || str_starts_with($line, "\u{200F}"), "Line not RTL: {$line}");
        }

        // Sections the prescription does not have are left out
        $this->assertStringNotContainsString('الأشعة', $message);
    }

    public function test_long_prescriptions_are_sent_in_parts_under_the_whatsapp_limit(): void
    {
        $prescription = $this->prescription('2026-11-08');

        foreach (range(1, 45) as $i) {
            $prescription->medicines()->attach(
                Medicine::create(['name' => "Medicine {$i} ".str_repeat('x', 20)])->id,
                ['dose' => 'قرص', 'frequency' => str_repeat('كل 8 ساعات ', 8), 'instructions' => str_repeat('تعليمات مفصلة ', 12)]
            );
        }

        $this->existingConversation(ConversationState::MAIN_MENU);
        $this->webhook(['type' => 'interactive', 'interactive' => ['type' => 'list_reply', 'list_reply' => ['id' => 'view_prescription']]]);

        $texts = Http::recorded()->map(fn ($pair) => $pair[0]['text']['body'] ?? null)->filter()->values();

        $this->assertGreaterThan(1, $texts->count());
        $texts->each(fn ($text) => $this->assertLessThanOrEqual(4096, mb_strlen($text)));
        $this->assertStringContainsString('Medicine 1 ', $texts->first());
        $this->assertStringContainsString('Medicine 45 ', $texts->last());

        // The main menu button still comes last
        Http::assertSent(fn (Request $request) => ($request['interactive']['action']['buttons'][0]['reply']['id'] ?? null) === 'back_to_mainmenu');
    }

    private function assertTemplateSent(string $template): void
    {
        Http::assertSent(function (Request $request) use ($template) {
            if (($request['template']['name'] ?? null) !== $template) {
                return false;
            }

            $components = collect($request['template']['components']);
            $body = collect($components->firstWhere('type', 'body')['parameters'])->pluck('text', 'parameter_name');
            $payloads = $components->where('sub_type', 'quick_reply')->map(fn ($button) => $button['parameters'][0]['payload'])->values()->all();

            return $request['to'] === '201000000002'
                && $request['template']['language']['code'] === 'ar_EG'
                && $body['name'] === 'Mona'
                && str_contains($body['date'], 'نوفمبر')
                && $payloads === [ConsultationReminder::BOOK_APPOINTMENT, ConsultationReminder::VIEW_PRESCRIPTION, ConsultationReminder::LATER];
        });
    }

    private function tapButton(string $payload): void
    {
        $this->webhook(['type' => 'button', 'button' => ['payload' => $payload, 'text' => 'x']]);
    }

    private function webhook(array $message): void
    {
        ConversationManager::execute([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'metadata' => ['phone_number_id' => 'pnid-1'],
                        'messages' => [['from' => '201000000002', 'id' => uniqid('wamid.'), ...$message]],
                    ],
                ]],
            ]],
        ]);
    }

    private function conversation(): WhatsAppConversation
    {
        return WhatsAppConversation::where('phone_number', '201000000002')->sole();
    }

    private function existingConversation(ConversationState $state, array $data = ['name' => 'Mona']): WhatsAppConversation
    {
        return WhatsAppConversation::create([
            'doctor_whatsapp_account_id' => $this->account->id,
            'phone_number' => '201000000002',
            'user_id' => $this->patient->user_id,
            'state' => $state,
            'data' => $data,
        ]);
    }

    private function appointment(string $date, AppointmentStatus $status, ?Patient $patient = null): Appointment
    {
        return Appointment::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => ($patient ?? $this->patient)->id,
            'date' => $date,
            'duration' => 30,
            'status' => $status,
        ]);
    }

    private function prescription(?string $consultationDate, ?Patient $patient = null, ?string $medicine = null, ?Appointment $appointment = null): Prescription
    {
        $patient ??= $this->patient;
        $appointment ??= $this->appointment('2026-10-25 10:00:00', AppointmentStatus::DONE, $patient);

        $prescription = Prescription::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $this->doctor->id,
            'patient_id' => $patient->id,
            'diagnosis' => 'Tonsillitis',
            'consultation_date' => $consultationDate,
        ]);

        if ($medicine) {
            $prescription->medicines()->attach(Medicine::create(['name' => $medicine])->id, ['dose' => '1 tablet']);
        }

        return $prescription;
    }

    private function user(UserType $type, string $name, string $phone): User
    {
        return User::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $phone.'@example.com',
            'password' => bcrypt('password'),
            'type' => $type,
        ]);
    }
}
