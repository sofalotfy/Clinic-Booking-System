<?php

namespace App\Models;

use App\Enums\LabSampleType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Lab extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $casts = [
        'sample_type' => LabSampleType::class,
        'fasting_required' => 'boolean',
        'is_active' => 'boolean',
    ];

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, ?string $term)
    {
        if (! $term) {
            return $query;
        }

        return $query->where(fn ($query) => $query
            ->where('name', 'like', "%{$term}%")
            ->orWhere('name_ar', 'like', "%{$term}%")
            ->orWhere('code', 'like', "%{$term}%"));
    }
}
