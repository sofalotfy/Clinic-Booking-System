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
                'access_token' => 'EABBbX1ZCpBBsBSKzKmRIQt8H6nfZAHapH81QQhZBCJQJhCTgZA0fQoSZAUb7mVgzMnJQ8Mc0GEiH9v4LyFHi4Vz3ZBzEFmMb6LkEQibl0JcZCKKKojkl4wAwVzZAk0JPdbBBXEIBWwXz1qnWIl7nqjmS6rQ4XYofYSY58YnAOKRJlW9SbwfAb4lm2f5qXRnt1kZAf7O6sOJDRbI9kYZCTLOI3tr2cAIKMqmYR5a8eFlvmvrO9oGrvdSk2wYnYFuZCZBT49RxWzkG7FltoSgZAsBnNi4KC4Q8b',
                'is_active' => true,
            ]
        );
    }
}