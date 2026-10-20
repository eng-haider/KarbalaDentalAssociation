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
        'is_featured', 'registration_open', 'registration_limit', 'professional_titles', 'professional_title_limits',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_featured' => 'boolean',
            'registration_open' => 'boolean',
            'registration_limit' => 'integer',
            'professional_titles' => 'array',
            'professional_title_limits' => 'array',
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

    /**
     * Seats for each professional title, set from the dashboard. A title
     * without a number stays open.
     *
     * @return array<string, int>
     */
    public function professionalTitleLimits(): array
    {
        $limits = [];

        foreach ($this->professional_title_limits ?? [] as $title => $limit) {
            if (is_numeric($limit) && $limit >= 0) {
                $limits[trim((string) $title)] = (int) $limit;
            }
        }

        return $limits;
    }

    /** @return array<string, int> Registrations so far for each professional title. */
    public function professionalTitleCounts(): array
    {
        return $this->registrations()
            ->whereNotNull('professional_title')
            ->selectRaw('professional_title, COUNT(*) as total')
            ->groupBy('professional_title')
            ->pluck('total', 'professional_title')
            ->map(fn ($total) => (int) $total)
            ->all();
    }

    /** @return list<string> Titles whose seats are all taken. */
    public function fullProfessionalTitles(): array
    {
        $counts = $this->professionalTitleCounts();

        return collect($this->professionalTitleLimits())
            ->filter(fn (int $limit, string $title) => ($counts[$title] ?? 0) >= $limit)
            ->keys()
            ->all();
    }

    /** Nothing left to register for: the overall limit is reached, or every professional title is full. */
    public function isFullyBooked(): bool
    {
        return $this->hasReachedRegistrationLimit()
            || array_diff($this->professionalTitleOptions(), $this->fullProfessionalTitles()) === [];
    }

    /** The overall limit set from the dashboard (e.g. 600) is reached; no limit means never. */
    public function hasReachedRegistrationLimit(): bool
    {
        return $this->registration_limit !== null
            && $this->registrations()->count() >= $this->registration_limit;
    }

    public function isProfessionalTitleFull(?string $title): bool
    {
        $limit = $this->professionalTitleLimits()[$title ?? ''] ?? null;

        return $limit !== null
            && $this->registrations()->where('professional_title', $title)->count() >= $limit;
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
