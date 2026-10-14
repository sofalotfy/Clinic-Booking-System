<?php

namespace Tests\Feature;

use App\Enums\AdminPermissionsEnum;
use App\Enums\AppointmentStatus;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Assistant;
use App\Models\Clinic;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Models\Role;
use App\Models\User;
use App\Services\Doctors\GetDoctorStatistics;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class DoctorShowPageTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);
    }

    public function test_statistics_cover_the_last_30_days_and_related_counts(): void
    {
        $doctor = $this->makeDoctor();
        $patient = $this->makePatient();
        $otherPatient = $this->makePatient();

        $this->makeAppointment($doctor, $patient, now()->subDays(3), AppointmentStatus::ACTIVE);
        $this->makeAppointment($doctor, $patient, now()->subDays(5), AppointmentStatus::DONE);
        $this->makeAppointment($doctor, $patient, now()->subDays(7), AppointmentStatus::CANCELLED);
        $this->makeAppointment($doctor, $otherPatient, now()->subDays(40), AppointmentStatus::ACTIVE);
        $this->makeAppointment($doctor, $patient, now()->addDays(4), AppointmentStatus::ACTIVE);
        $this->makeAppointment($doctor, $otherPatient, now()->addDays(40), AppointmentStatus::ACTIVE);

        PatientBlock::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'blocked_by' => $doctor->user_id,
            'blocked_at' => now(),
        ]);

        $assistantUser = $this->makeUser(UserType::ASSISTANT);
        Assistant::create(['doctor_id' => $doctor->id, 'user_id' => $assistantUser->id]);

        $stats = GetDoctorStatistics::execute($doctor);

        $this->assertSame(3, $stats['appointments_total']);
        $this->assertSame(1, $stats['appointments_by_status'][AppointmentStatus::ACTIVE->value]);
        $this->assertSame(1, $stats['appointments_by_status'][AppointmentStatus::DONE->value]);
        $this->assertSame(1, $stats['appointments_by_status'][AppointmentStatus::CANCELLED->value]);
        $this->assertSame(1, $stats['appointments_upcoming_total']);
        $this->assertSame(1, $stats['appointments_upcoming_by_status'][AppointmentStatus::ACTIVE->value]);
        $this->assertSame(0, $stats['appointments_upcoming_by_status'][AppointmentStatus::DONE->value]);
        $this->assertSame(2, $stats['patients']);
        $this->assertSame(1, $stats['blocked_patients']);
        $this->assertSame(1, $stats['assistants']);
    }

    public function test_show_page_renders_form_data_and_statistics(): void
    {
        $doctor = $this->makeDoctor();
        Clinic::create([
            'doctor_id' => $doctor->id,
            'name' => 'Bright Smile Clinic',
            'location_link' => 'maps.example.com/clinic',
        ]);

        $role = $this->makeGlobalRole('Doctor Viewer', [
            [AdminPermissionsEnum::VIEW_SINGLE_DOCTOR->value, PermissionsTypeEnum::ADMIN],
        ]);

        $admin = $this->makeUser(UserType::ADMIN);
        $admin->assignRole($role);

        $this->actingAs($admin)
            ->get(route('web-admin.doctors.show', $doctor->id))
            ->assertOk()
            ->assertSee($doctor->user->email)
            ->assertSee('Bright Smile Clinic')
            ->assertSee('Appointments (last 30 days)')
            ->assertSee('Appointments (next 30 days)')
            ->assertSee('Patients')
            ->assertSee('Blocked patients')
            ->assertSee('Assistants')
            ->assertSee('Created')
            ->assertSee($doctor->clinic->created_at->format('Y-m-d'))
            ->assertSee('href="https://maps.example.com/clinic"', false);
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
        $user = User::create([
            'name' => 'Doctor '.uniqid(),
            'email' => uniqid('doctor').'@example.com',
            'phone' => '2'.random_int(100000000, 999999999),
            'password' => bcrypt('password'),
            'type' => UserType::DOCTOR,
        ]);

        return Doctor::create(['user_id' => $user->id]);
    }

    private function makePatient(): Patient
    {
        return Patient::create(['user_id' => $this->makeUser(UserType::PATIENT)->id]);
    }

    private function makeAppointment(Doctor $doctor, Patient $patient, $date, AppointmentStatus $status): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => $date,
            'duration' => 30,
            'status' => $status,
        ]);
    }

    private function makeGlobalRole(string $name, array $permissions): Role
    {
        $role = Role::create([
            'name' => $name,
            'guard_name' => 'web',
            'doctor_id' => null,
        ]);

        foreach ($permissions as [$permissionName, $type]) {
            $permission = Permission::query()
                ->where('name', $permissionName)
                ->where('type', $type)
                ->firstOrFail();

            $role->givePermissionTo($permission);
        }

        return $role;
    }
}
