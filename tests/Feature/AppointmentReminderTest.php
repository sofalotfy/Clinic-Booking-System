<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\NotificationEnum;
use App\Enums\PermissionsTypeEnum;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Support\ArabicDateFormatter;
use Carbon\Carbon;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AppointmentReminderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Registered per test rather than in setUp: Http::fake() keeps the first
     * stub registered for a URL pattern, so a later fake cannot override it.
     */
    private function fakeGraph(int $status = 200): void
    {
        Http::fake([
            'graph.facebook.com/*' => Http::response(
                $status === 200 ? ['success' => true] : ['error' => 'rejected'],
                $status
            ),
        ]);
    }

    public function test_it_reminds_the_patient_about_a_today_appointment()
    {
        $this->fakeGraph();
        $fixture = $this->fixture(now()->setTime(14, 30));

        $this->artisan('app:send-appointment-reminders')
            ->assertExitCode(0);

        $notification = Notification::where('receiver_id', $fixture['patientUser']->id)->first();

        $this->assertNotNull($notification);
        $this->assertSame(
            NotificationEnum::PATIENT_DAY_APPOINTMENT_REMINDER->title(),
            $notification->title
        );
    }

    public function test_it_sends_a_stateless_whatsapp_message_using_the_reminder_template()
    {
        $this->fakeGraph();
        $fixture = $this->fixture(now()->setTime(14, 30));
        $this->createWhatsAppAccount($fixture);

        $this->artisan('app:send-appointment-reminders')->assertExitCode(0);

        Http::assertSent(function ($request) use ($fixture) {
            return str_contains($request->url(), 'graph.facebook.com')
                && ($request['template']['name'] ?? null) === 'patient_day_appointment_reminder'
                && $this->bodyParameters($request) === [
                    ['name' => 'name', 'value' => $fixture['patientUser']->name],
                    ['name' => 'time', 'value' => ArabicDateFormatter::formatTime(
                        Carbon::parse($fixture['appointment']->date)->format('H:i')
                    )],
                ];
        });
    }

    /**
     * Named body parameters, keyed by parameter_name, so a placeholder rename
     * in the handler shows up here rather than as a rejected WhatsApp send.
     */
    private function bodyParameters($request): array
    {
        return collect($request['template']['components'] ?? [])
            ->filter(fn ($component) => ($component['type'] ?? null) === 'body')
            ->flatMap(fn ($component) => $component['parameters'] ?? [])
            ->map(fn ($parameter) => [
                'name' => $parameter['parameter_name'] ?? null,
                'value' => $parameter['text'] ?? null,
            ])
            ->values()
            ->all();
    }

    public function test_it_only_notifies_the_patient_and_not_the_doctor()
    {
        $this->fakeGraph();
        $fixture = $this->fixture(now()->setTime(14, 30));

        $this->artisan('app:send-appointment-reminders')->assertExitCode(0);

        $this->assertSame(1, Notification::count());
        $this->assertSame(
            1,
            Notification::where('receiver_id', $fixture['patientUser']->id)->count()
        );
        $this->assertSame(
            0,
            Notification::where('receiver_id', $fixture['doctorUser']->id)->count()
        );
    }

    public function test_it_ignores_appointments_on_other_days()
    {
        $this->fakeGraph();
        $this->fixture(now()->addDay()->setTime(14, 30));

        $this->artisan('app:send-appointment-reminders')->assertExitCode(0);

        $this->assertSame(0, Notification::count());
    }

    public function test_it_ignores_cancelled_and_completed_appointments()
    {
        $this->fakeGraph();
        $cancelled = $this->fixture(now()->setTime(14, 30));
        $cancelled['appointment']->update(['status' => AppointmentStatus::CANCELLED]);

        $done = $this->fixture(now()->setTime(15, 30));
        $done['appointment']->update(['status' => AppointmentStatus::DONE]);

        $this->artisan('app:send-appointment-reminders')->assertExitCode(0);

        $this->assertSame(0, Notification::count());
    }

    public function test_it_can_target_an_explicit_date()
    {
        $this->fakeGraph();
        $this->fixture(now()->addDay()->setTime(14, 30));

        $this->artisan('app:send-appointment-reminders', [
            'date' => now()->addDay()->toDateString(),
        ])->assertExitCode(0);

        $this->assertSame(1, Notification::count());
    }

    public function test_it_rejects_an_unparsable_date()
    {
        $this->artisan('app:send-appointment-reminders', ['date' => 'not-a-date'])
            ->assertExitCode(1);
    }

    public function test_it_reports_when_there_is_nothing_to_remind()
    {
        $this->artisan('app:send-appointment-reminders')
            ->expectsOutputToContain('No appointments to remind')
            ->assertExitCode(0);
    }

    public function test_the_reminder_permission_is_seeded_but_unused()
    {
        $this->seed(PermissionTableSeeder::class);

        $permission = Permission::where('name', 'patient_day_appointment_reminder_notifications')->first();

        $this->assertNotNull($permission);
        $this->assertSame(PermissionsTypeEnum::NOTIFICATION->value, $permission->type);
        $this->assertTrue(NotificationEnum::PATIENT_DAY_APPOINTMENT_REMINDER->notifiesPatient());
        $this->assertFalse(
            NotificationEnum::PATIENT_DAY_APPOINTMENT_REMINDER->notifiesClinic(),
            'Reminder is patient only, so the clinic permission is never queried.'
        );
    }

    public function test_a_failed_graph_call_is_recorded_as_failed_not_sent()
    {
        $this->fakeGraph(500);
        $fixture = $this->fixture(now()->setTime(14, 30));
        $this->createWhatsAppAccount($fixture);

        $this->artisan('app:send-appointment-reminders')->assertExitCode(0);

        $this->assertSame('failed', Notification::first()->status->value);
    }

    private function createWhatsAppAccount(array $fixture): DoctorWhatsAppAccount
    {
        return DoctorWhatsAppAccount::create([
            'doctor_id' => $fixture['doctor']->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
            'is_active' => true,
        ]);
    }

    private function fixture($date): array
    {
        $unique = uniqid();

        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'phone' => '111'.$unique,
            'email' => "doctor+{$unique}@example.com",
            'password' => bcrypt('password'),
        ]);

        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'phone' => '222'.$unique,
            'email' => "patient+{$unique}@example.com",
            'password' => bcrypt('password'),
        ]);

        $patient = Patient::create(['user_id' => $patientUser->id]);

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => $date,
            'duration' => 30,
        ]);

        return compact('doctorUser', 'doctor', 'patientUser', 'patient', 'appointment');
    }
}
