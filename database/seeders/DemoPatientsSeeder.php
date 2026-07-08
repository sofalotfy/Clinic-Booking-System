<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Enums\AppointmentStatus;

use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Enums\UserType;

class DemoPatientsSeeder extends Seeder
{
    public function run(): void
    {
        $doctorId = 1;

        for ($i = 1; $i <= 10; $i++) {

            $user = User::create([
                'name' => "Patient {$i}",
                'email' => "patient{$i}@example.com",
                'password' => Hash::make('password'),
                'phone' => '01000000' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'type' => UserType::PATIENT->value,
            ]);

            // Same logic as your register() method
            $patient = Patient::create([
                'user_id' => $user->id,
            ]);

            Appointment::create([
                'doctor_id' => $doctorId,
                'patient_id' => $patient->id,
                'status' => AppointmentStatus::ACTIVE->value,
                'date' => Carbon::now()->addDays($i),
                'delay' => 0,
                'duration' => 30,
            ]);
        }
    }
}