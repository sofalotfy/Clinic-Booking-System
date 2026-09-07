<?php

namespace Database\Seeders;

use App\Models\Doctor;
use App\Models\Appointment;
use App\Enums\AppointmentStatus;
use App\Services\Patients\Retrievals\ListDoctorPatients;
use Illuminate\Database\Seeder;
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

            foreach ($patients as $patient) {
                Appointment::create([
                    'doctor_id'  => $doctor->id,
                    'patient_id' => $patient->id,
                    'status'     => AppointmentStatus::ACTIVE->value,
                    'date'       => $this->randomDateWithinNextWeek(),
                    'delay'      => 0,
                    'duration'   => 30,
                ]);
            }

            $this->command->info("Seeded {$patients->count()} appointments for doctor #{$doctor->id}");
        }
    }

    private function randomDateWithinNextWeek(): Carbon
    {
        return Carbon::now()
            ->addDays(rand(0, 7))
            ->setTime(rand(9, 17), collect([0, 30])->random());
    }
}