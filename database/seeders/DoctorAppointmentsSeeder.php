<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Services\Patients\Retrievals\ListDoctorPatients;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class DoctorAppointmentsSeeder extends Seeder
{
    private const MAX_APPOINTMENTS_PER_DOCTOR = 30;

    public function run(): void
    {
        $doctors = Doctor::all();

        if ($doctors->isEmpty()) {
            $this->command->warn('No doctors found.');
            return;
        }

        foreach ($doctors as $doctor) {
            $patients = ListDoctorPatients::execute($doctor->user)
                ->limit(self::MAX_APPOINTMENTS_PER_DOCTOR)
                ->get();

            if ($patients->isEmpty()) {
                $this->command->warn("No patients found for doctor #{$doctor->id}, skipping.");
                continue;
            }

            $rows = $patients->map(fn ($patient) => [
                'doctor_id'  => $doctor->id,
                'patient_id' => $patient->id,
                'status' => AppointmentStatus::ACTIVE->value,
                'date'       => $this->randomDate(),
                'delay' => 0,
                'duration' => 30,
                'created_at' => now(),
                'updated_at' => now(),
            ])->all();

            Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $patient->id,
                'status' => AppointmentStatus::ACTIVE->value,
                'date' => Carbon::now()
                    ->addDays($data['days_from_now'])
                    ->setTime($data['hour'], $data['minute']),
                'delay' => 0,
                'duration' => 30,
            ]);
            DB::table('appointments')->insert($rows);

            $this->command->info("Seeded {$patients->count()} appointments for doctor #{$doctor->id}");
        }
    }

    private function randomDate(): Carbon
    {
        return now()->addDays(rand(-60, 60))->setTime(rand(9, 17), 0);
    }
