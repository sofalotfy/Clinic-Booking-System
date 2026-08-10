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
                'access_token' => 'EABBbX1ZCpBBsBSDR8Sj6YnXwLjRQd9QZCCfYeO7e8bHIGS7ze7YqL8SZBF4GdOAT0k7ZByZA3pba674N1JeRMl9oIYP2seDsrt8BZAMIdMZCu0Dl8rMrvAJhlQteZCeqh6v8kYw02bXKMZAkTvtKnYjCWfraThNIAtfgdRRKHNZBZBrfZAuOHP6hSe4ULCZA1NURxL6b37PrZB4gUgstGImOICjZCwi2SkREf2G9uXszHxM8TRR3xFb6MdhmW9XLtdM45mSjwJ1E57Jkqsy9vEvFbYVQLyGcGKbBAZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}