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
                'access_token' => 'EABBbX1ZCpBBsBSD9mTdBKk78O3P9hZCp2oux6PwC7y8nefK4Y8wZBtZAJYOZChTo0ujj82GQopHCMlwUhKZBNMV21ArK0PCrAtPjHswtymKYhljpWl8vNVjSMZC8iTqvmXL5qBie7JM4GxnNcZAEgBnldVcl1TNdcsLrbEr4m7V8ehv4SnNMTchXJjRe9dtU6yR7OTcv8uFbbXFijKd6Mprd2J8wqHyMiYPCNd3ueuv3ajyV9Jw29Qziy5RjahTDpZAffVe6hvLyNJpOsXjI8svh6lLxXhQZDZD',
                'is_active' => true,
                //commit
            ]
        );
    }
}