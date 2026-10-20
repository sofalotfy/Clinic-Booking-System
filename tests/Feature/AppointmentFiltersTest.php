<?php

namespace Tests\Feature;

use App\Enums\AppointmentGrade;
use App\Enums\AppointmentStatus;
use App\Enums\Gender;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Services\Appointments\Retrievals\ListAppointments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentFiltersTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private array $ids = [];

    protected function setUp(): void
    {
        parent::setUp();

        $this->doctor = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Doctor', '100')->id]);
        $mona = Patient::create(['user_id' => $this->user(UserType::PATIENT, 'Mona Ali', '201111', Gender::FEMALE)->id]);
        $omar = Patient::create(['user_id' => $this->user(UserType::PATIENT, 'Omar Said', '202222', Gender::MALE)->id]);

        $this->ids['mona_active'] = $this->appointment($mona, '2026-11-01 10:00:00', AppointmentStatus::ACTIVE, confirmed: true)->id;
        $this->ids['mona_done'] = $this->appointment($mona, '2026-11-02 11:00:00', AppointmentStatus::DONE, AppointmentGrade::DONEINTIME)->id;
        $this->ids['omar_pending'] = $this->appointment($omar, '2026-11-03 12:00:00', AppointmentStatus::PENDING, confirmed: false)->id;
        $this->ids['omar_cancelled'] = $this->appointment($omar, '2026-11-04 09:00:00', AppointmentStatus::CANCELLED, AppointmentGrade::PATIENTCANCEL)->id;

        Prescription::create(['appointment_id' => $this->ids['mona_done'], 'doctor_id' => $this->doctor->id, 'patient_id' => $mona->id]);

        // Another clinic's appointment never shows up
        $other = Doctor::create(['user_id' => $this->user(UserType::DOCTOR, 'Other', '300')->id]);
        Appointment::create(['doctor_id' => $other->id, 'patient_id' => $mona->id, 'date' => '2026-11-01 10:00:00', 'duration' => 30, 'status' => AppointmentStatus::ACTIVE]);
    }

    public function test_each_filter_narrows_the_list(): void
    {
        $cases = [
            [[], ['mona_active', 'mona_done', 'omar_pending', 'omar_cancelled']],
            [['date' => '2026-11-02'], ['mona_done']],
            [['date_from' => '2026-11-02', 'date_to' => '2026-11-03'], ['mona_done', 'omar_pending']],
            [['status' => ['Active', 'Pending']], ['mona_active', 'omar_pending']],
            [['grade' => ['Patient Cancel']], ['omar_cancelled']],
            [['is_confirmed' => false], ['omar_pending']],
            [['gender' => 'Female'], ['mona_active', 'mona_done']],
            [['search' => 'omar'], ['omar_pending', 'omar_cancelled']],
            [['search' => '201111'], ['mona_active', 'mona_done']],
            [['has_prescription' => true], ['mona_done']],
            [['has_prescription' => false, 'status' => ['Done', 'Cancelled']], ['omar_cancelled']],
        ];

        foreach ($cases as [$filters, $expected]) {
            $this->assertEqualsCanonicalizing(
                array_map(fn ($key) => $this->ids[$key], $expected),
                $this->list($filters),
                'Filters: '.json_encode($filters)
            );
        }
    }

    public function test_sort_orders_by_date(): void
    {
        $this->assertSame(
            [$this->ids['omar_cancelled'], $this->ids['omar_pending'], $this->ids['mona_done'], $this->ids['mona_active']],
            $this->list(['sort' => 'desc'])
        );
    }

    public function test_api_rejects_invalid_filters(): void
    {
        $doctorUser = $this->doctor->user;

        foreach (['status=Unknown', 'status=Active,Nope', 'date=01-11-2026', 'gender=x', 'sort=up', 'date_from=2026-11-05&date_to=2026-11-01'] as $query) {
            $this->actingAs($doctorUser, 'sanctum')
                ->getJson('/api/appointments?'.$query)
                ->assertUnprocessable();
        }
    }

    private function list(array $filters): array
    {
        return ListAppointments::execute($this->doctor->user, $filters)
            ->pluck('appointments.id')
            ->all();
    }

    private function appointment(Patient $patient, string $date, AppointmentStatus $status, ?AppointmentGrade $grade = null, bool $confirmed = true): Appointment
    {
        return Appointment::create([
            'doctor_id' => $this->doctor->id,
            'patient_id' => $patient->id,
            'date' => $date,
            'duration' => 30,
            'status' => $status,
            'grade' => $grade,
            'isConfirmed' => $confirmed,
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
