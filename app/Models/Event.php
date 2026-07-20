<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    protected $fillable = [
        'title', 'description', 'location', 'starts_at', 'ends_at',
        'is_featured', 'registration_open',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_featured' => 'boolean',
            'registration_open' => 'boolean',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->where('starts_at', '>=', now())->orderBy('starts_at');
    }

    /**
     * The event shown in the homepage banner: the one explicitly flagged as
     * featured, otherwise simply the next one on the calendar.
     */
    public static function featured(): ?self
    {
        return static::upcoming()->where('is_featured', true)->first()
            ?? static::upcoming()->first();
    }
}
