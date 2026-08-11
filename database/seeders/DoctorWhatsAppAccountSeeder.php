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
                'access_token' => 'EABBbX1ZCpBBsBSPUp8larUeWbROGmgn2eGdWZABAhjrRAIW6Pn4c9HF48dtmwoqSu3QweFAgs8wDwC9ZCRwHg1GOkKZAu1lDtaE8lvgIOgXm9xQUtSWw2SUjh3OC3rLiRlXUc5mIjMAY6t9eGCMvuitAt0kjdElfZCh7OfdsVyNCkKTNKJBJgtkc9P4X9mlZCDaAsfp4jHrst0Luw07jjoAIDlFj9LPKDuU9TRZBZC9QeA0Obbi5UPJfG6lCKfd2anFjzPa8E0lvQkZBUgAGafTu4gQyO',
                'is_active' => true,
                //commit
            ]
        );
    }
}