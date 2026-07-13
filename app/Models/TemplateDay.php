<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\TemplatePlan;

class TemplateDay extends Model
{
    protected $fillable = ['template_plan_id', 'day_of_week', 'start_time', 'end_time', 'appointment_duration', 'queue_length'];

    /**
     * Get the TemplatePlan for this template day schedule.
     */
    public function templatePlan(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(TemplatePlan::class);
    }
}
