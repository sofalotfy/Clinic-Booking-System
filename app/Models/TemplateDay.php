<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TemplatePlan;

class TemplateDay extends Model
{
    protected $guarded = [];

    /**
     * Get the TemplatePlan for this template day schedule.
     */
    public function templatePlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TemplatePlan::class);
    }
}
