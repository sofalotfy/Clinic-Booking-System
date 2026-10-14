<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UpdateAppointmentDateTest extends TestCase
{
    use RefreshDatabase;

    public function test_update_moves_the_appointment_to_a_full_new_date(): void
    {
        [$doctorUser, $appointment] = $this->makeAppointment();

        $this->actingAs($doctorUser, 'sanctum')
            ->putJson("/api/appointments/{$appointment->id}", [
                'status' => AppointmentStatus::ACTIVE->value,
                'date' => '2026-12-05',
                'time' => '14:30',
            ])
            ->assertOk();

        $this->assertSame('2026-12-05 14:30:00', $appointment->fresh()->date);
    }

    public function test_date_and_time_can_be_sent_alone(): void
    {
        [$doctorUser, $appointment] = $this->makeAppointment();

        $this->actingAs($doctorUser, 'sanctum')
            ->putJson("/api/appointments/{$appointment->id}", ['date' => '2026-12-05'])
            ->assertOk();
        $this->assertSame('2026-12-05 10:00:00', $appointment->fresh()->date);

        $this->actingAs($doctorUser, 'sanctum')
            ->putJson("/api/appointments/{$appointment->id}", ['time' => '11:15'])
            ->assertOk();
        $this->assertSame('2026-12-05 11:15:00', $appointment->fresh()->date);
    }

    public function test_a_combined_datetime_in_date_is_rejected(): void
    {
        [$doctorUser, $appointment] = $this->makeAppointment();

        $this->actingAs($doctorUser, 'sanctum')
            ->putJson("/api/appointments/{$appointment->id}", ['date' => '2026-12-05 14:30'])
            ->assertUnprocessable();
    }

    public function test_bulk_update_moves_appointments_to_full_new_dates(): void
    {
        [$doctorUser, $appointment] = $this->makeAppointment();

        $this->actingAs($doctorUser, 'sanctum')
            ->putJson('/api/appointments', [
                'updates' => [['id' => $appointment->id, 'changes' => ['date' => '2026-12-07', 'time' => '09:00']]],
            ])
            ->assertOk();

        $this->assertSame('2026-12-07 09:00:00', $appointment->fresh()->date);
    }

    private function makeAppointment(): array
    {
        $doctorUser = User::create([
            'name' => 'Doctor', 'email' => 'd@example.com', 'phone' => '1000000001',
            'password' => bcrypt('password'), 'type' => UserType::DOCTOR,
        ]);
        $doctor = Doctor::create(['user_id' => $doctorUser->id]);

        $patientUser = User::create([
            'name' => 'Patient', 'email' => 'p@example.com', 'phone' => '1000000002',
            'password' => bcrypt('password'), 'type' => UserType::PATIENT,
        ]);
        $patient = Patient::create(['user_id' => $patientUser->id]);

        $appointment = Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => '2026-12-01 10:00:00',
            'duration' => 30,
            'status' => AppointmentStatus::ACTIVE,
        ]);

        return [$doctorUser, $appointment];
    }
}
