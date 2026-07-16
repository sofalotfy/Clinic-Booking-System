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
                'access_token' => 'EABBbX1ZCpBBsBR0ZBO1fNuFHtC3NTbZAOZAHzIBVuocn87B0JrF8qJcjMyRWM6W12ABDDSkyD3XKZBqyDFuWRf7ZBBAH2ZBHQqixeEPwSByU6vIZApVUZCRiJ6txmbfP5wmSn15hVyQOObVG8cD6ObnGMZCEZClsDXLT2V53X21fpZAm0lURljylqV7JDXoOuoM6rFRhSu3AZCSA4mJpZCSdmzqs2FS4J6eertfxkVeU1aWB16nr63EvGcpZAhG5ZBtY47dKHrjBa8cHW69u7IbXLJhUSPMUK0tgZCHYk',
                'is_active' => true,
                //commit
            ]
        );
    }
}