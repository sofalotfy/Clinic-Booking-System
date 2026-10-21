<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AppointmentType;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\User;
use App\Services\Appointments\Checks\ResolveAppointmentType;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentTypeTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private Patient $patient;

    protected function setUp(): void
    {
        parent::setUp();

        $this->travelTo('2026-10-21 09:00:00');
        $this->doctor = Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id]);
        $this->patient = Patient::create(['user_id' => $this->makeUser(UserType::PATIENT)->id]);
    }

    public function test_no_prescription_is_a_visit(): void
    {
        $this->assertSame(AppointmentType::VISIT, $this->resolve('2026-11-01'));
    }

    public function test_new_patient_is_a_visit(): void
    {
        $this->assertSame(AppointmentType::VISIT, ResolveAppointmentType::execute($this->doctor->id, null, '2026-11-01'));
    }

    public function test_on_or_before_the_consultation_date_is_a_consultation(): void
    {
        $this->prescribe('2026-11-10');

        $this->assertSame(AppointmentType::CONSULTATION, $this->resolve('2026-11-05'));
        $this->assertSame(AppointmentType::CONSULTATION, $this->resolve('2026-11-10 15:00'));
    }

    public function test_after_the_consultation_date_is_a_visit(): void
    {
        $this->prescribe('2026-11-10');

        $this->assertSame(AppointmentType::VISIT, $this->resolve('2026-11-11'));
    }

    public function test_prescription_without_consultation_date_is_a_visit(): void
    {
        $this->prescribe(null);

        $this->assertSame(AppointmentType::VISIT, $this->resolve('2026-11-05'));
    }

    public function test_only_the_latest_prescription_counts(): void
    {
        $this->prescribe('2026-12-01');
        $this->prescribe('2026-11-01');

        $this->assertSame(AppointmentType::VISIT, $this->resolve('2026-11-20'));
    }

    public function test_another_doctors_prescription_is_ignored(): void
    {
        $other = Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id]);
        $this->prescribe('2026-11-10', $other);

        $this->assertSame(AppointmentType::VISIT, $this->resolve('2026-11-05'));
    }

    private function resolve(string $date): AppointmentType
    {
        return ResolveAppointmentType::execute($this->doctor->id, $this->patient, $date);
    }

    private function prescribe(?string $consultationDate, ?Doctor $doctor = null): Prescription
    {
        $doctor ??= $this->doctor;

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $this->patient->id,
            'date' => '2026-10-15 10:00:00',
            'duration' => 30,
            'status' => AppointmentStatus::DONE,
        ]);

        return Prescription::create([
            'appointment_id' => $appointment->id,
            'doctor_id' => $doctor->id,
            'patient_id' => $this->patient->id,
            'consultation_date' => $consultationDate,
        ]);
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
}
