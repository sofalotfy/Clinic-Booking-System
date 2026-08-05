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
                'access_token' => 'EABBbX1ZCpBBsBSDKVpQSGbeQZCCJzH4tJiCujdtEW9LQYaaZA0BgGf6MYaDPjNok82y15UJSTga3kUliyyeoVlqGOZAjTiebsOibEmHS1SSsYwZCfF9N8OyaTzLFph4GZBMxG29EWyemqy3w3se8ogaDcoGfuFY15ZCJl2Rux5vwIfk2I6SU7aD5zpFT53r4mjS0VZCyxNcZALtTfJZA7jsLohS7H7MgpqUw9F80xfaZCK215rYQxbWY1dqFJxoW8awPtv424vZAOwF3PUFemTbSa65sn97VjLwZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}