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
                'access_token' => 'EABBbX1ZCpBBsBSEr2lsBeNOBk3pSPAYXnkSjhMlW1HqPAscvk0aGp6fBls4OWbdn50ZCxBlg8VCAHjt9WwOZBM7LUOdOcjbUYi5CfHvbvwB4uI17GEH44Oq1ZAJCRLZCUzZCHeC2eA5HZAPOSQZAWQDTeDjpsZAsT4Ndjp6odmeZBjtsvZChqAITSzZCcUSxgjJfhtehDCGgALw7mnmedh8GZBbz8iGlKgNWeMPt8BffnBlgBTARpUCridrKZCcZBv3yib7m6fZC9L3LlWaeOBq5i0hmnvEvAkLK',
                'is_active' => true,
                //commit
            ]
        );
    }
}