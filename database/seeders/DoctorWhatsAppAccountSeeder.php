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
                'access_token' => 'EABBbX1ZCpBBsBSJkrHvrrZC7XCARc1yfvDEzbcZBIbNXlUMicFuyvEpnyuJ9wxS7Jyg8H6L7RgzZABFtCHX5gOy7GIoDFEugB2GznEnW7Rx5gQbeQhXOdOhYZCtKhzxoPPjbKiXQZBWDhAmoLUJDNHWpNrb4XZAUTARsypXei6LNSP3sShuujAk8gZAVTj1m4OBDefBxlWZBzpndpTZC4aflbzCn6ZCJeZCPvZBN2wh0o2sZABQvee2JPvNtr4KZBL2lTv4qKNiRZBnaZBghIqP1jCOx2ujw0yGOl',
                'is_active' => true,
                //commit
            ]
        );
    }
}