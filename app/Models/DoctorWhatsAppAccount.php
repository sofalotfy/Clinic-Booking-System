<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;

class DoctorWhatsAppAccount extends Model
{
    protected $table = 'doctor_whatsapp_accounts';
    
    protected $casts = [
        'access_token' => 'encrypted',
    ];

    protected $fillable = [
        'doctor_id',
        'phone_number_id',
        'access_token',
        'is_active',
    ];

    public function doctor()
    {
        return $this->belongsTo(Doctor::class);
    }
}