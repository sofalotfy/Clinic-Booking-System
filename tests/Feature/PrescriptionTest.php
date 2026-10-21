<?php

namespace Tests\Feature;

use App\Enums\AppointmentStatus;
use App\Enums\AssistantPermissionsEnum;
use App\Enums\PermissionsTypeEnum;
use App\Enums\UserType;
use App\Models\Appointment;
use App\Models\Assistant;
use App\Models\Doctor;
use App\Models\Lab;
use App\Models\Medicine;
use App\Models\Patient;
use App\Models\Prescription;
use App\Models\Role;
use App\Models\Scan;
use App\Models\User;
use Database\Seeders\PermissionTableSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class PrescriptionTest extends TestCase
{
    use RefreshDatabase;

    private Doctor $doctor;

    private Appointment $appointment;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(PermissionTableSeeder::class);

        $this->doctor = $this->makeDoctor();
        $this->appointment = $this->makeAppointment($this->doctor);
    }

    public function test_doctor_writes_a_prescription_for_an_appointment(): void
    {
        $panadol = Medicine::create(['name' => 'Panadol Extra', 'strength' => '500 mg']);
        $augmentin = Medicine::create(['name' => 'Augmentin 1g']);
        $cbc = Lab::create(['name' => 'CBC']);
        $xray = Scan::create(['name' => 'Chest X-Ray']);

        $response = $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", [
                'diagnosis' => 'Acute tonsillitis',
                'notes' => 'Plenty of fluids',
                'consultation_date' => '2026-12-10',
                'medicines' => [
                    ['id' => $augmentin->id, 'dose' => '1 tablet', 'frequency' => 'every 12 hours', 'duration' => '7 days', 'instructions' => 'after meals'],
                    ['id' => $panadol->id, 'dose' => '1 tablet', 'frequency' => 'when needed'],
                ],
                'labs' => [['id' => $cbc->id, 'notes' => 'after the antibiotic course']],
                'scans' => [['id' => $xray->id]],
            ])
            ->assertCreated()
            ->assertJsonPath('prescription.appointment_id', $this->appointment->id)
            ->assertJsonPath('prescription.patient.id', $this->appointment->patient_id)
            ->assertJsonPath('prescription.consultation_date', '2026-12-10')
            // Kept in the order the doctor entered them
            ->assertJsonPath('prescription.medicines.0.name', 'Augmentin 1g')
            ->assertJsonPath('prescription.medicines.0.frequency', 'every 12 hours')
            ->assertJsonPath('prescription.medicines.1.name', 'Panadol Extra')
            ->assertJsonPath('prescription.labs.0.notes', 'after the antibiotic course')
            ->assertJsonPath('prescription.scans.0.name', 'Chest X-Ray');

        $prescription = Prescription::sole();
        $this->assertSame($this->doctor->id, $prescription->doctor_id);
        $this->assertSame(AppointmentStatus::DONE, $this->appointment->fresh()->status);

        $this->actingAs($this->doctor->user, 'sanctum')
            ->getJson("/api/appointments/{$this->appointment->id}/prescription")
            ->assertOk()
            ->assertJsonPath('prescription.id', $prescription->id);

        $this->actingAs($this->doctor->user, 'sanctum')
            ->getJson("/api/prescriptions/{$prescription->id}")
            ->assertOk()
            ->assertJsonPath('prescription.diagnosis', 'Acute tonsillitis');
    }

    public function test_an_appointment_gets_only_one_prescription_and_never_when_cancelled(): void
    {
        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['diagnosis' => 'first'])
            ->assertCreated();

        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['diagnosis' => 'second'])
            ->assertJsonValidationErrors('appointment');

        $cancelled = $this->makeAppointment($this->doctor, AppointmentStatus::CANCELLED);

        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$cancelled->id}/prescription", ['diagnosis' => 'x'])
            ->assertJsonValidationErrors('appointment');

        $this->assertSame(1, Prescription::count());
        $this->assertSame(AppointmentStatus::CANCELLED, $cancelled->fresh()->status);
    }

    public function test_a_rejected_prescription_leaves_the_appointment_untouched(): void
    {
        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['consultation_date' => '2026-11-30'])
            ->assertJsonValidationErrors('consultation_date');

        $this->assertSame(AppointmentStatus::ACTIVE, $this->appointment->fresh()->status);
    }

    public function test_consultation_date_must_be_after_the_appointment_day(): void
    {
        foreach (['2026-12-01', '2026-11-30'] as $date) {
            $this->actingAs($this->doctor->user, 'sanctum')
                ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['consultation_date' => $date])
                ->assertJsonValidationErrors('consultation_date');
        }

        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['consultation_date' => '2026-12-02'])
            ->assertCreated();
    }

    public function test_items_must_exist_in_the_catalog_and_not_repeat(): void
    {
        $deleted = Medicine::create(['name' => 'Gone']);
        $deleted->delete();
        $panadol = Medicine::create(['name' => 'Panadol']);

        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", [
                'medicines' => [['id' => $deleted->id], ['id' => 999999]],
            ])
            ->assertJsonValidationErrors(['medicines.0.id', 'medicines.1.id']);

        $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", [
                'medicines' => [['id' => $panadol->id], ['id' => $panadol->id]],
            ])
            ->assertJsonValidationErrors('medicines.0.id');
    }

    public function test_update_replaces_only_the_lists_that_are_sent(): void
    {
        $panadol = Medicine::create(['name' => 'Panadol']);
        $brufen = Medicine::create(['name' => 'Brufen']);
        $cbc = Lab::create(['name' => 'CBC']);

        $id = $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", [
                'diagnosis' => 'Flu',
                'medicines' => [['id' => $panadol->id, 'dose' => '1 tablet']],
                'labs' => [['id' => $cbc->id]],
            ])
            ->json('prescription.id');

        $this->actingAs($this->doctor->user, 'sanctum')
            ->putJson("/api/prescriptions/{$id}", [
                'consultation_date' => '2026-12-15',
                'medicines' => [['id' => $brufen->id, 'dose' => '2 tablets']],
            ])
            ->assertOk()
            ->assertJsonPath('prescription.diagnosis', 'Flu')
            ->assertJsonPath('prescription.consultation_date', '2026-12-15')
            ->assertJsonCount(1, 'prescription.medicines')
            ->assertJsonPath('prescription.medicines.0.name', 'Brufen')
            ->assertJsonCount(1, 'prescription.labs');

        $this->actingAs($this->doctor->user, 'sanctum')
            ->putJson("/api/prescriptions/{$id}", ['labs' => [], 'consultation_date' => null])
            ->assertOk()
            ->assertJsonCount(0, 'prescription.labs')
            ->assertJsonPath('prescription.consultation_date', null);
    }

    public function test_medicines_removed_from_the_catalog_still_show_on_old_prescriptions(): void
    {
        $panadol = Medicine::create(['name' => 'Panadol']);

        $id = $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['medicines' => [['id' => $panadol->id]]])
            ->json('prescription.id');

        $panadol->delete();

        $this->actingAs($this->doctor->user, 'sanctum')
            ->getJson("/api/prescriptions/{$id}")
            ->assertJsonPath('prescription.medicines.0.name', 'Panadol');
    }

    public function test_list_filters_and_is_scoped_to_the_clinic(): void
    {
        $withConsultation = $this->makeAppointment($this->doctor);
        Prescription::create(['appointment_id' => $this->appointment->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->appointment->patient_id]);
        Prescription::create(['appointment_id' => $withConsultation->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $withConsultation->patient_id, 'consultation_date' => '2026-12-20']);

        $other = $this->makeDoctor();
        $otherAppointment = $this->makeAppointment($other);
        Prescription::create(['appointment_id' => $otherAppointment->id, 'doctor_id' => $other->id, 'patient_id' => $otherAppointment->patient_id]);

        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/prescriptions')
            ->assertOk()
            ->assertJsonPath('prescriptions.total', 2);

        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/prescriptions?has_consultation=1')
            ->assertJsonPath('prescriptions.total', 1)
            ->assertJsonPath('prescriptions.data.0.consultation_date', '2026-12-20');

        $this->actingAs($this->doctor->user, 'sanctum')->getJson('/api/prescriptions?patient_id='.$this->appointment->patient_id)
            ->assertJsonPath('prescriptions.total', 1);
    }

    public function test_other_clinics_cannot_read_write_or_delete(): void
    {
        $prescription = Prescription::create(['appointment_id' => $this->appointment->id, 'doctor_id' => $this->doctor->id, 'patient_id' => $this->appointment->patient_id]);
        $intruder = $this->makeDoctor()->user;

        $this->actingAs($intruder, 'sanctum')->getJson("/api/prescriptions/{$prescription->id}")->assertForbidden();
        $this->actingAs($intruder, 'sanctum')->putJson("/api/prescriptions/{$prescription->id}", ['diagnosis' => 'x'])->assertForbidden();
        $this->actingAs($intruder, 'sanctum')->deleteJson("/api/prescriptions/{$prescription->id}")->assertForbidden();
        $this->actingAs($intruder, 'sanctum')->getJson("/api/appointments/{$this->appointment->id}/prescription")->assertForbidden();
        $this->actingAs($intruder, 'sanctum')
            ->postJson('/api/appointments/'.$this->makeAppointment($this->doctor)->id.'/prescription', [])
            ->assertForbidden();

        $this->assertSame(1, Prescription::count());
    }

    public function test_assistants_need_the_prescription_permissions(): void
    {
        $assistant = $this->makeUser(UserType::ASSISTANT);
        Assistant::create(['doctor_id' => $this->doctor->id, 'user_id' => $assistant->id]);

        $this->actingAs($assistant, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", [])
            ->assertForbidden();

        $role = Role::create(['name' => 'Nurse', 'guard_name' => 'web', 'doctor_id' => $this->doctor->id]);
        $role->givePermissionTo(Permission::where('name', AssistantPermissionsEnum::CREATE_PRESCRIPTION->value)
            ->where('type', PermissionsTypeEnum::ASSISTANT)->firstOrFail());
        $assistant->assignRole($role);

        $this->actingAs($assistant->fresh(), 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['diagnosis' => 'ok'])
            ->assertCreated();
    }

    public function test_doctor_can_delete_a_prescription(): void
    {
        $medicine = Medicine::create(['name' => 'Panadol']);
        $id = $this->actingAs($this->doctor->user, 'sanctum')
            ->postJson("/api/appointments/{$this->appointment->id}/prescription", ['medicines' => [['id' => $medicine->id]]])
            ->json('prescription.id');

        $this->actingAs($this->doctor->user, 'sanctum')->deleteJson("/api/prescriptions/{$id}")->assertOk();

        $this->assertSame(0, Prescription::count());
        $this->assertDatabaseCount('medicine_prescription', 0);
        $this->assertModelExists($medicine);
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
        return Doctor::create(['user_id' => $this->makeUser(UserType::DOCTOR)->id])->load('user');
    }

    private function makeAppointment(Doctor $doctor, AppointmentStatus $status = AppointmentStatus::ACTIVE): Appointment
    {
        $patient = Patient::create(['user_id' => $this->makeUser(UserType::PATIENT)->id]);

        return Appointment::create([
            'doctor_id' => $doctor->id,
            'patient_id' => $patient->id,
            'date' => '2026-12-01 10:00:00',
            'duration' => 30,
            'status' => $status,
        ]);
    }
}
