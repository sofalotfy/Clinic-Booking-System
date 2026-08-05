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
                'access_token' => 'EABBbX1ZCpBBsBSN3pnz2OaYFh3uueKMspUZCCunsTPRYZCOqqLCgRocXxglOV3Fj15f7H30dCndO8edPqGKPm6Ngosh0MeZAZBUZBZCUsrsUZBNtjp1rLn9IXcYeZAdEh23bNDSONZB34pv5HOyop5QgR6Ag8ovbh3EoUZAx4ZCBywsU8JDnhcQNnMVcytK6TqzzDMopJrFtJZCIpobb8vympgqRrYgBXoYUIjGzEuVegZBf8zJjqgEBUASeMPGPGpTOZAHvBLZAXzX8D5eVZATY8mzXdT21xetSULQZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}