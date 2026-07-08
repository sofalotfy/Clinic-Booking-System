<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\DoctorWhatsAppAccount;

class DoctorWhatsAppAccountSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DoctorWhatsAppAccount::updateOrCreate(
            ['doctor_id' => 1],
            [
                'phone_number_id' => '1137805152755860',
                'access_token' => 'EABBbX1ZCpBBsBRwPPfqDUS7k05zZAw3lg8kUFcU5bsB7faPgrDwhwCQmZAzSmUtZCNKfKm3r3xuxdFO84hlVJY6dh4oMBxqyLwQ6kVrKHsYthuPPYoxRNt3QeO6KSOZAK7Y3Ovpby9vYOlWv1PgYjRAbLBsDjUXQqOLdp8lm9Bs7O47W6Wth2y7LpvTpXn5l0I55JEp5jIU2qbFxfdXaRuPrB6lCCaZC2bOTl7O6f8wfeZBxEpcJufsNPbOIHgocKKOjwXyS2MBIQdfkYKSHr3nZCnct',
                'is_active' => true,
            ]
        );
    }
}