<?php

namespace Tests\Feature;

use App\APIServices\WhatsApp\ConversationManager;
use App\Enums\AdminPermissionsEnum;
use App\Enums\AppointmentStatus;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Assistant;
use App\Models\Doctor;
use App\Models\DoctorWhatsAppAccount;
use App\Models\Patient;
use App\Models\Role;
use App\Models\Suspension;
use App\Models\User;
use App\Models\WhatsAppConversation;
use App\Services\Doctors\ReinstateDoctor;
use App\Services\Doctors\SuspendDoctor;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DoctorSuspensionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);

        Http::fake([
            'graph.facebook.com/*' => Http::response(['success' => true], 200),
        ]);
    }

    public function test_admin_delete_suspends_the_doctor_instead_of_deleting(): void
    {
        $doctor = $this->makeDoctor();
        $doctor->user->createToken('api-token');
        $admin = $this->makeAdmin([AdminPermissionsEnum::SUSPEND_DOCTOR]);

        $this->actingAs($admin)
            ->delete(route('web-admin.doctors.destroy', $doctor->id), ['reason' => 'Unpaid subscription'])
            ->assertRedirect()
            ->assertSessionHas('status', 'Doctor suspended successfully.');

        $this->assertModelExists($doctor);
        $this->assertTrue($doctor->fresh()->isSuspended());
        $this->assertSame(0, $doctor->user->tokens()->count());

        $suspension = $doctor->suspensions()->sole();
        $this->assertSame('Unpaid subscription', $suspension->reason);
        $this->assertSame($admin->id, $suspension->suspended_by);
        $this->assertNull($suspension->lifted_at);
    }

    public function test_suspending_twice_keeps_a_single_active_suspension(): void
    {
        $doctor = $this->makeDoctor();

        SuspendDoctor::execute($doctor);
        SuspendDoctor::execute($doctor);

        $this->assertSame(1, $doctor->suspensions()->count());
    }

    public function test_reinstating_lifts_the_suspension_and_keeps_the_log(): void
    {
        $doctor = $this->makeDoctor();
        $admin = $this->makeAdmin([AdminPermissionsEnum::REINSTATE_DOCTOR]);

        SuspendDoctor::execute($doctor);

        $this->actingAs($admin)
            ->post(route('web-admin.doctors.reinstate', $doctor->id))
            ->assertRedirect()
            ->assertSessionHas('status', 'Doctor reinstated successfully.');

        $this->assertFalse($doctor->fresh()->isSuspended());

        $suspension = $doctor->suspensions()->sole();
        $this->assertNotNull($suspension->lifted_at);
        $this->assertSame($admin->id, $suspension->lifted_by);

        SuspendDoctor::execute($doctor->fresh());
        $this->assertSame(2, $doctor->suspensions()->count());
    }

    public function test_suspend_and_reinstate_require_their_permissions(): void
    {
        $doctor = $this->makeDoctor();
        $admin = $this->makeAdmin([AdminPermissionsEnum::VIEW_SINGLE_DOCTOR]);

        $this->actingAs($admin)
            ->delete(route('web-admin.doctors.destroy', $doctor->id))
            ->assertForbidden();

        $this->actingAs($admin)
            ->post(route('web-admin.doctors.reinstate', $doctor->id))
            ->assertForbidden();

        $this->assertSame(0, Suspension::count());
    }

    public function test_show_page_lists_the_suspension_log(): void
    {
        $doctor = $this->makeDoctor();
        $admin = $this->makeAdmin([AdminPermissionsEnum::VIEW_SINGLE_DOCTOR, AdminPermissionsEnum::REINSTATE_DOCTOR]);

        SuspendDoctor::execute($doctor, $admin, 'Policy violation');

        $this->actingAs($admin)
            ->get(route('web-admin.doctors.show', $doctor->id))
            ->assertOk()
            ->assertSee('Suspended')
            ->assertSee('Policy violation')
            ->assertSee('Reinstate Doctor');
    }

    public function test_suspended_doctor_and_assistants_cannot_log_in(): void
    {
        $doctor = $this->makeDoctor();
        $assistant = $this->makeAssistant($doctor);

        SuspendDoctor::execute($doctor);

        foreach ([$doctor->user, $assistant] as $user) {
            $this->postJson('/api/login', ['email' => $user->email, 'password' => 'password'])
                ->assertForbidden();
        }

        ReinstateDoctor::execute($doctor);

        $this->postJson('/api/login', ['email' => $doctor->user->email, 'password' => 'password'])
            ->assertOk()
            ->assertJsonStructure(['token']);
    }

    public function test_suspended_clinic_tokens_are_rejected_by_the_api(): void
    {
        $doctor = $this->makeDoctor();
        $assistant = $this->makeAssistant($doctor);

        SuspendDoctor::execute($doctor);

        foreach ([$doctor->user, $assistant] as $user) {
            $token = $user->createToken('api-token')->plainTextToken;

            $this->withToken($token)->getJson('/api/me')->assertForbidden();
        }
    }

    public function test_whatsapp_bot_of_a_suspended_doctor_does_not_start_conversations(): void
    {
        $doctor = $this->makeDoctor();
        DoctorWhatsAppAccount::create([
            'doctor_id' => $doctor->id,
            'phone_number_id' => '123456789',
            'access_token' => 'dummy_token',
        ]);

        SuspendDoctor::execute($doctor);

        ConversationManager::execute([
            'entry' => [[
                'changes' => [[
                    'value' => [
                        'metadata' => ['phone_number_id' => '123456789'],
                        'messages' => [[
                            'from' => '966500000000',
                            'id' => 'wamid.suspended',
                            'type' => 'text',
                            'text' => ['body' => 'hi'],
                        ]],
                    ],
                ]],
            ]],
        ]);

        Http::assertSent(fn ($request) => $request['to'] === '966500000000'
            && str_contains($request['text']['body'] ?? '', 'غير متاحة'));

        $this->assertSame(0, WhatsAppConversation::count());
        $this->assertNull(User::where('phone', '966500000000')->first());
    }

    public function test_reminders_skip_suspended_doctors(): void
    {
        $doctor = $this->makeDoctor();
        $patient = Patient::create(['user_id' => $this->makeUser(UserType::PATIENT)->id]);

        Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => today()->setTime(12, 0),
            'duration' => 30,
            'status' => AppointmentStatus::ACTIVE,
        ]);

        SuspendDoctor::execute($doctor);

        $this->artisan('app:send-appointment-reminders')
            ->expectsOutputToContain('No appointments to remind')
            ->assertSuccessful();
    }

    private function makeUser(UserType $type): User
    {
        return User::create([
            'name' => 'User '.uniqid(),
            'email' => uniqid('user').'@example.com',
            'phone' => '1'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => $type,
        ]);
    }

    private function makeDoctor(): Doctor
    {
        return Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id]);
    }

    private function makeAssistant(Doctor $doctor): User
    {
        $user = $this->makeUser(UserType::ASSISTANT);
        Assistant::create(['doctor_id' => $doctor->id, 'user_id' => $user->id]);

        return $user;
    }

    private function makeAdmin(array $permissions): User
    {
        $role = Role::create([
            'name' => 'Role '.uniqid(),
            'guard_name' => 'web',
            'doctor_id' => null,
        ]);

        foreach ($permissions as $permission) {
            $role->givePermissionTo(
                Permission::query()
                    ->where('name', $permission->value)
                    ->where('type', PermissionsTypeEnum::ADMIN)
                    ->firstOrFail()
            );
        }

        $admin = $this->makeUser(UserType::ADMIN);
        $admin->assignRole($role);

        return $admin;
    }
}
