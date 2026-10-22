<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCategory extends Model
{
    protected $guarded = [];

    public function costs(): HasMany
    {
        return $this->hasMany(Cost::class);
    }
}
