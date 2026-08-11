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
                'access_token' => 'EABBbX1ZCpBBsBSDgKjvGxpKMnTWbS3FskWaqc7Vyi9c5orFyC78ZCJidgz706xauPjBaUrt1ov7hjDuJhB2k4pvnZAItaMhWfRWGZAnwayjuWxQMQZAQlCKbZAPbETEQlUDgM4iKUJCNCOtBU33bg1FdZAEOl9doQltXRLPDQo3YVLlZC6739OT9ZC5D9dklNpaZBcDVBepZAjkhF4YYI7oZAerIbGvuQEt6VSYCJO56ZCUhtmRJ3xUwtjLuraVmGmLtK33aiTAwSBLDz9hK1RhGvwawOWWVK',
                'is_active' => true,
                //commit
            ]
        );
    }
}