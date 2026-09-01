<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Doctor;
use App\Enums\TemplatePlanStatus;

class TemplatePlan extends Model
{
    protected $guarded = [];

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

    /**
     * Get the template days for this template plan.
     */
    public function templateDays(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(TemplateDay::class);
    }

}
