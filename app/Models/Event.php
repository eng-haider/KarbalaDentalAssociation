<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    public const OSIF_SLUG = 'osif-karbala-2026';

    /** Used until the choices are edited from the dashboard. */
    public const DEFAULT_PROFESSIONAL_TITLES = [
        'طبيب أسنان اختصاص',
        'طبيب أسنان مقيم أقدم',
        'طبيب أسنان تدرج',
        'طبيب أسنان مقيم',
        'طبيب أسنان خريج جديد',
    ];

    protected $fillable = [
        'title', 'slug', 'description', 'location', 'starts_at', 'ends_at',
        'is_featured', 'registration_open', 'professional_titles',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_featured' => 'boolean',
            'registration_open' => 'boolean',
            'professional_titles' => 'array',
        ];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(EventRegistration::class);
    }

    public function isOsif(): bool
    {
        return $this->slug === self::OSIF_SLUG;
    }

    /** @return list<string> */
    public function professionalTitleOptions(): array
    {
        $titles = array_values(array_filter(
            array_map(fn ($title) => trim((string) $title), $this->professional_titles ?? []),
            'filled',
        ));

        return $titles ?: self::DEFAULT_PROFESSIONAL_TITLES;
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
