<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\Gender;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Flag;
use App\Models\Patient;
use App\Models\PatientBlock;
use App\Models\User;
use App\Services\Patients\Retrievals\ListDoctorPatients;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientListFiltersTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private array $patients = [];

    private array $flags = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Doctor', '100')->id]);

        foreach (['vip' => Gender::FEMALE, 'diabetic' => Gender::MALE, 'late' => Gender::FEMALE, 'plain' => Gender::MALE] as $name => $gender) {
            $this->patients[$name] = $this->patient($this->doctor, $name, $gender);
        }

        foreach (['vip', 'diabetic', 'urgent'] as $name) {
            $this->flags[$name] = Flag::create(['doctor_id' => $this->doctor->id, 'name' => $name, 'color' => 'red']);
        }

        // vip: vip + diabetic, diabetic: diabetic, late: urgent, plain: none
        $this->flag('vip', 'vip', $this->doctor);
        $this->flag('vip', 'diabetic', $this->doctor);
        $this->flag('diabetic', 'diabetic', $this->doctor);
        $this->flag('late', 'urgent', $this->doctor);
    }

    public function test_flags_any_matches_patients_with_at_least_one_flag(): void
    {
        $this->assertSame(['diabetic', 'vip'], $this->list(['flags' => [$this->flags['diabetic']->id]]));
        $this->assertSame(['diabetic', 'late', 'vip'], $this->list(['flags' => [$this->flags['diabetic']->id, $this->flags['urgent']->id]]));
        $this->assertSame(['diabetic', 'late', 'vip'], $this->list(['flags' => [$this->flags['diabetic']->id, $this->flags['urgent']->id], 'flag_match' => 'any']));
    }

    public function test_flags_all_requires_every_flag(): void
    {
        $this->assertSame(['vip'], $this->list(['flags' => [$this->flags['vip']->id, $this->flags['diabetic']->id], 'flag_match' => 'all']));
        $this->assertSame([], $this->list(['flags' => [$this->flags['vip']->id, $this->flags['urgent']->id], 'flag_match' => 'all']));
    }

    public function test_no_flags_returns_unflagged_patients(): void
    {
        $this->assertSame(['plain'], $this->list(['no_flags' => true]));
        $this->assertSame(['diabetic', 'late', 'plain', 'vip'], $this->list(['no_flags' => false]));
    }

    public function test_flags_can_be_combined_with_other_filters(): void
    {
        $this->assertSame(['vip'], $this->list(['flags' => [$this->flags['diabetic']->id], 'gender' => 'Female']));
        $this->assertSame(['late', 'vip'], $this->list(['gender' => 'Female']));
    }

    public function test_only_this_clinics_flags_count(): void
    {
        // Another clinic flagged the same patient with its own flag, and the
        // patient also visits that clinic. Neither leaks into this listing.
        $other = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Other', '300')->id]);
        $otherFlag = Flag::create(['doctor_id' => $other->id, 'name' => 'vip', 'color' => 'blue']);
        $this->appointment($other, $this->patients['plain']);
        $this->patients['plain']->flags()->attach($otherFlag->id, ['doctor_id' => $other->id]);

        $this->assertSame(['plain'], $this->list(['no_flags' => true]));
        $this->assertSame([], $this->list(['flags' => [$otherFlag->id]]));
    }

    public function test_is_blocked_filter(): void
    {
        PatientBlock::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patients['late']->id, 'blocked_at' => now()]);
        PatientBlock::create(['doctor_id' => $this->doctor->id, 'patient_id' => $this->patients['plain']->id, 'blocked_at' => now(), 'unblocked_at' => now()]);

        $this->assertSame(['late'], $this->list(['is_blocked' => true]));
        $this->assertSame(['diabetic', 'plain', 'vip'], $this->list(['is_blocked' => false]));
    }

    public function test_api_validates_the_new_filters(): void
    {
        $other = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Other', '300')->id]);
        $foreignFlag = Flag::create(['doctor_id' => $other->id, 'name' => 'x', 'color' => 'red']);

        foreach ([
            'flags='.$foreignFlag->id,
            'flags=99999',
            'flags=abc',
            'flags='.$this->flags['vip']->id.'&flag_match=some',
            'gender=x',
            'is_blocked=maybe',
        ] as $query) {
            $this->actingAs($this->doctor->user, 'sanctum')
                ->getJson('/api/patients?'.$query)
                ->assertUnprocessable();
        }
    }

    private function list(array $filters): array
    {
        $names = ListDoctorPatients::execute($this->doctor->user, $filters)
            ->get()
            ->map(fn ($patient) => $patient->name)
            ->sort()
            ->values()
            ->all();

        return $names;
    }

    private function flag(string $patient, string $flag, Doctor $doctor): void
    {
        $this->patients[$patient]->flags()->attach($this->flags[$flag]->id, ['doctor_id' => $doctor->id]);
    }

    private function patient(Doctor $doctor, string $name, Gender $gender): Patient
    {
        $patient = Patient::create(['user_id' => $this->user(UserType::PATIENT, $name, '2'.random_int(10000, 99999), $gender)->id]);
        $this->appointment($doctor, $patient);

        return $patient;
    }

    private function appointment(Doctor $doctor, Patient $patient): Appointment
    {
        return Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => '2026-11-01 10:00:00',
            'duration' => 30,
            'status' => AppointmentStatus::ACTIVE,
        ]);
    }

    private function user(UserType $type, string $name, string $phone, ?Gender $gender = null): User
    {
        return User::create([
            'name' => $name,
            'phone' => $phone,
            'email' => $phone.'@example.com',
            'password' => bcrypt('password'),
            'type' => $type,
            'gender' => $gender,
        ]);
    }
}
