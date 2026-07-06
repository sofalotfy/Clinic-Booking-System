<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use App\Enums\TemplatePlanStatus;

class TemplatePlan extends Model
{
    protected $fillable = ['doctor_id', 'status'];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => TemplatePlanStatus::class,
        ];
    }

    /**
     * Get the doctor for this template plan.
     */
    public function doctor(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Doctor::class);
    }
}
