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
                'access_token' => 'EABBbX1ZCpBBsBR8RyZCXIbD0Yr9ZBRq5UdTBFHKm3h3QyZCmmpuydThXBpRujsCNKiTdmitwk5DKjZAwAe64YcYNx5ZCmDQ1zb8Eo6ZBfZAwbCDZBLFn9SmRDifjefCtPeb1thxG6BZAAgqX2UZBl48fH6tBRnuHZAboKwlzCoFcJfmJkASroemfwP89j5eals6tTXJszhfYsWZCQRMvzRYfWAvCHqJbkDbdk5HMWUeUeb3WtCTsY9kNjz98GvFjhcywnIZCvekzzIkgwr28zZA5CLbwN8qEOXZC',
                'is_active' => true,
                //commit
            ]
        );
    }
}