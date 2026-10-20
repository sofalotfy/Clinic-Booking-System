<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MedicineImport extends Model
{
    protected $guarded = [];

    protected $casts = [
        'errors' => 'array',
    ];

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}
