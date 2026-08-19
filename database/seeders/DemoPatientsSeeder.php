<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Carbon\Carbon;
use App\Enums\AppointmentStatus;

use App\Models\Doctor;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;
use App\Enums\UserType;

class DemoPatientsSeeder extends Seeder
{
    public function run(): void
    {
        $doctor = User::create([
                'name' => "Shokry Makram",
                'email' => "Shokry@gmail.com",
                'password' => Hash::make('password'),
                'phone' => '01000000000',
                'type' => UserType::DOCTOR->value,
            ]);

        $doctor = Doctor::create([
            'user_id'  => $doctor->id,
        ]);

        $doctorId = $doctor->id;

        $patients = [
            [
                'name' => 'Ahmed Hassan',
                'email' => 'ahmed.hassan@example.com',
                'phone' => '01012345678',
                'days_from_now' => 1,
                'hour' => 10,
                'minute' => 0,
            ],
            [
                'name' => 'Mariam Mohamed',
                'email' => 'mariam.mohamed@example.com',
                'phone' => '01123456789',
                'days_from_now' => 1,
                'hour' => 11,
                'minute' => 30,
            ],
            [
                'name' => 'Omar Ibrahim',
                'email' => 'omar.ibrahim@example.com',
                'phone' => '01234567890',
                'days_from_now' => 2,
                'hour' => 9,
                'minute' => 30,
            ],
            [
                'name' => 'Nour Khaled',
                'email' => 'nour.khaled@example.com',
                'phone' => '01098765432',
                'days_from_now' => 2,
                'hour' => 13,
                'minute' => 0,
            ],
            [
                'name' => 'Youssef Adel',
                'email' => 'youssef.adel@example.com',
                'phone' => '01198765432',
                'days_from_now' => 3,
                'hour' => 10,
                'minute' => 30,
            ],
            [
                'name' => 'Salma Tarek',
                'email' => 'salma.tarek@example.com',
                'phone' => '01287654321',
                'days_from_now' => 4,
                'hour' => 12,
                'minute' => 0,
            ],
            [
                'name' => 'Karim Mostafa',
                'email' => 'karim.mostafa@example.com',
                'phone' => '01076543210',
                'days_from_now' => 5,
                'hour' => 9,
                'minute' => 0,
            ],
            [
                'name' => 'Hana Mahmoud',
                'email' => 'hana.mahmoud@example.com',
                'phone' => '01165432109',
                'days_from_now' => 6,
                'hour' => 14,
                'minute' => 30,
            ],
            [
                'name' => 'Mostafa Samir',
                'email' => 'mostafa.samir@example.com',
                'phone' => '01254321098',
                'days_from_now' => 7,
                'hour' => 11,
                'minute' => 0,
            ],
            [
                'name' => 'Aya Sherif',
                'email' => 'aya.sherif@example.com',
                'phone' => '01043210987',
                'days_from_now' => 8,
                'hour' => 15,
                'minute' => 0,
            ],
        ];

        foreach ($patients as $data) {

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make('password'),
                'phone' => $data['phone'],
                'type' => UserType::PATIENT->value,
            ]);

            $patient = Patient::create([
                'user_id' => $user->id,
            ]);

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
        }
    }
}