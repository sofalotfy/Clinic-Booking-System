<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class Medicine extends Model
{
    use SoftDeletes;

    protected $guarded = [];

    protected $hidden = ['match_key'];

    protected $casts = [
        'price' => 'decimal:2',
        'unit_price' => 'decimal:2',
        'major_units' => 'integer',
        'minor_units' => 'integer',
        'is_active' => 'boolean',
    ];

    protected static function booted(): void
    {
        // Keep the match key in step with the name for rows added or renamed
        // from the admin panel, so the next sheet upload finds them.
        static::saving(function (Medicine $medicine) {
            $medicine->match_key = self::matchKey($medicine->name);
        });
    }

    /**
     * Normalized trade name used to match sheet rows to existing medicines:
     * case, repeated whitespace and surrounding spaces are ignored.
     */
    public static function matchKey(?string $name): string
    {
        return Str::of((string) $name)->squish()->lower()->toString();
    }

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
            ->orWhere('active_ingredient', 'like', "%{$term}%")
            ->orWhere('barcode', $term));
    }
}
