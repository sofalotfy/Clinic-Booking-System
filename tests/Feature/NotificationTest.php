<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\States\Notifications\Appointments\DoctorAppointmentBooking;
use App\Enums\NotificationEnum;
use App\Enums\NotificationStatus;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Notification;
use App\Models\Patient;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Services\Notifications\NotificationManager;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class NotificationTest extends TestCase
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

    public function test_index_returns_only_the_authenticated_users_unread_notifications()
    {
        $fixture = $this->appointmentFixture();

        $mine = $this->createNotification($fixture['doctorUser'], $fixture['doctor']);
        $this->createNotification($fixture['doctorUser'], $fixture['doctor'], ['viewed' => true]);
        $theirs = $this->createNotification($fixture['patientUser'], $fixture['doctor']);

        $response = $this->actingAs($fixture['doctorUser'])->getJson('/api/notifications');

        $response->assertOk();
        $response->assertJsonCount(1, 'notifications');
        $this->assertSame($mine->id, $response->json('notifications.0.id'));
        $this->assertNotSame($theirs->id, $response->json('notifications.0.id'));
    }

    public function test_historical_returns_all_notifications_for_the_authenticated_user()
    {
        $fixture = $this->appointmentFixture();

        $this->createNotification($fixture['doctorUser'], $fixture['doctor']);
        $this->createNotification($fixture['doctorUser'], $fixture['doctor'], ['viewed' => true]);
        $this->createNotification($fixture['patientUser'], $fixture['doctor']);

        $response = $this->actingAs($fixture['doctorUser'])->getJson('/api/notifications/historical');

        $response->assertOk();
        $response->assertJsonCount(2, 'notifications');
    }

    public function test_viewing_a_notification_marks_it_viewed()
    {
        $fixture = $this->appointmentFixture();

        $notification = $this->createNotification($fixture['doctorUser'], $fixture['doctor']);

        $response = $this->actingAs($fixture['doctorUser'])
            ->postJson("/api/notifications/{$notification->id}");

        $response->assertOk();
        $this->assertTrue($notification->fresh()->viewed);
    }

    public function test_viewing_a_notification_belonging_to_someone_else_is_forbidden()
    {
        $fixture = $this->appointmentFixture();

        $theirs = $this->createNotification($fixture['patientUser'], $fixture['doctor']);

        $this->actingAs($fixture['doctorUser'])
            ->postJson("/api/notifications/{$theirs->id}")
            ->assertForbidden();

        $this->assertFalse($theirs->fresh()->viewed);
    }

    public function test_viewing_notifications_in_bulk_only_touches_the_authenticated_users_rows()
    {
        $fixture = $this->appointmentFixture();

        $first = $this->createNotification($fixture['doctorUser'], $fixture['doctor']);
        $second = $this->createNotification($fixture['doctorUser'], $fixture['doctor']);
        $theirs = $this->createNotification($fixture['patientUser'], $fixture['doctor']);

        $response = $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', [
                'ids' => [$first->id, $second->id, $theirs->id],
            ]);

        $response->assertOk();
        $response->assertJsonCount(2, 'notifications');

        // The response is re-read from the database, so it reflects the write.
        foreach ($response->json('notifications') as $payload) {
            $this->assertTrue($payload['viewed']);
        }

        $this->assertTrue($first->fresh()->viewed);
        $this->assertTrue($second->fresh()->viewed);
        $this->assertFalse($theirs->fresh()->viewed);
    }

    public function test_viewing_notifications_in_bulk_with_no_matching_rows_returns_not_found()
    {
        $fixture = $this->appointmentFixture();

        $theirs = $this->createNotification($fixture['patientUser'], $fixture['doctor']);

        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', ['ids' => [$theirs->id]])
            ->assertNotFound();
    }

    public function test_viewing_notifications_in_bulk_twice_still_succeeds()
    {
        $fixture = $this->appointmentFixture();

        $notification = $this->createNotification($fixture['doctorUser'], $fixture['doctor']);

        $payload = ['ids' => [$notification->id]];

        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', $payload)
            ->assertOk();

        // Idempotent: re-sending the same request must not 404 just because
        // the rows were already viewed and therefore not "changed".
        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', $payload)
            ->assertOk();

        $this->assertTrue($notification->fresh()->viewed);
    }

    public function test_viewing_notifications_in_bulk_succeeds_when_they_are_already_viewed()
    {
        $fixture = $this->appointmentFixture();

        $notification = $this->createNotification($fixture['doctorUser'], $fixture['doctor'], [
            'viewed' => true,
        ]);

        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', ['ids' => [$notification->id]])
            ->assertOk();
    }

    public function test_viewing_notifications_in_bulk_requires_an_array_of_ids()
    {
        $fixture = $this->appointmentFixture();

        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', ['ids' => 'not-an-array'])
            ->assertStatus(400);

        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', ['ids' => []])
            ->assertStatus(400);

        $this->actingAs($fixture['doctorUser'])
            ->postJson('/api/notifications/view-bulk', [])
            ->assertStatus(400);
    }

    public function test_notification_is_marked_sent_when_the_clinic_has_no_whatsapp_account()
    {
        $fixture = $this->appointmentFixture();

        $this->dispatchDoctorBooked($fixture);

        $this->assertSame(1, Notification::count());
        $this->assertSame(NotificationStatus::SENT, Notification::first()->status);
    }

    public function test_notification_is_marked_sent_when_whatsapp_delivery_succeeds()
    {
        $this->fakeGraph();

        $fixture = $this->appointmentFixture();
        $this->createWhatsAppAccount($fixture);

        $this->dispatchDoctorBooked($fixture);

        $this->assertSame(NotificationStatus::SENT, Notification::first()->status);
        $this->assertNull(Notification::first()->failure_reason);
    }

    public function test_notification_is_marked_failed_when_whatsapp_delivery_fails()
    {
        $this->fakeGraph(401);

        $fixture = $this->appointmentFixture();
        $this->createWhatsAppAccount($fixture);

        $this->dispatchDoctorBooked($fixture);

        $notification = Notification::first();
        $this->assertSame(NotificationStatus::FAILED, $notification->status);
        $this->assertSame('WhatsApp channel reported a failure', $notification->failure_reason);
    }

    public function test_whatsapp_delivery_failure_does_not_bubble_up_to_the_caller()
    {
        $this->fakeGraph(500);

        $fixture = $this->appointmentFixture();
        $this->createWhatsAppAccount($fixture);

        // Would previously have been an exception from inside the appointment transaction.
        $this->assertTrue($this->dispatchDoctorBooked($fixture));
        $this->assertSame(1, Notification::count());
        $this->assertSame(NotificationStatus::FAILED, Notification::first()->status);
    }

    public function test_stateful_whatsapp_notification_stores_the_notification_id_on_the_conversation()
    {
        $this->fakeGraph();

        $fixture = $this->appointmentFixture();
        $this->createWhatsAppAccount($fixture);

        $this->dispatchDoctorBooked($fixture);

        $conversation = WhatsAppConversation::first();
        $this->assertSame(Notification::first()->id, $conversation->data['notification_id']);
    }

    public function test_confirming_over_whatsapp_marks_the_linked_notification_viewed()
    {
        $this->fakeGraph();

        $fixture = $this->appointmentFixture();
        $this->createWhatsAppAccount($fixture);

        $this->dispatchDoctorBooked($fixture);

        $notification = Notification::first();
        $this->assertFalse($notification->viewed);

        DoctorAppointmentBooking::handleResponse(
            WhatsAppConversation::first(),
            [
                'type' => 'interactive',
                'value' => 'confirm',
                'from' => $fixture['patientUser']->phone,
            ]
        );

        $this->assertTrue($notification->fresh()->viewed);
    }

    private function dispatchDoctorBooked(array $fixture): bool
    {
        return NotificationManager::execute(
            $fixture['doctorUser'],
            $fixture['doctor']->id,
            NotificationEnum::DOCTOR_APPOINTMENT_BOOKED,
            $fixture['appointment'],
        );
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

    private function createNotification(User $receiver, Doctor $doctor, array $attributes = []): Notification
    {
        return Notification::create(array_merge([
            'receiver_id' => $receiver->id,
            'doctor_id' => $doctor->id,
            'title' => 'Test notification',
            'text' => 'Test body',
            'status' => NotificationStatus::SENT,
        ], $attributes));
    }

    private function appointmentFixture(): array
    {
        $doctorUser = User::create([
            'name' => 'Doctor Name',
            'phone' => '1111111111',
            'email' => 'doctor@example.com',
            'password' => bcrypt('password'),
        ]);

        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        $patientUser = User::create([
            'name' => 'John Doe',
            'phone' => '2222222222',
            'email' => 'patient@example.com',
            'password' => bcrypt('password'),
        ]);

        $patient = Patient::create(['user_id' => $patientUser->id]);

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => now()->addDay(),
            'duration' => 30,
        ]);

        return compact('doctorUser', 'doctor', 'patientUser', 'patient', 'appointment');
    }
}
