<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class JobOpening extends Model
{
    public const TYPE_FULL_TIME = 'full_time';

    public const TYPE_PART_TIME = 'part_time';

    public const TYPE_LOCUM = 'locum';

    public const TYPE_TRAINING = 'training';

    protected $fillable = [
        'title', 'employer', 'type', 'specialty', 'city', 'description', 'requirements',
        'salary', 'contact_name', 'contact_phone', 'contact_email', 'apply_link',
        'logo', 'closes_at', 'is_featured', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'closes_at' => 'date',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Only what the dashboard published, and only while it is still open:
     * a vacancy past its deadline drops off the site on its own.
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query
            ->where('is_active', true)
            ->where(fn (Builder $q) => $q
                ->whereNull('closes_at')
                ->orWhereDate('closes_at', '>=', now()->toDateString()))
            ->orderByDesc('is_featured')
            ->orderBy('sort_order')
            ->orderByDesc('created_at');
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $q) => $q->where('type', $type));
    }

    /** Work arrangements a clinic or centre can advertise. */
    public static function types(): array
    {
        return [
            self::TYPE_FULL_TIME => 'دوام كامل',
            self::TYPE_PART_TIME => 'دوام جزئي',
            self::TYPE_LOCUM => 'بديل / مناوبة',
            self::TYPE_TRAINING => 'تدريب وإقامة',
        ];
    }

    public static function typeIcons(): array
    {
        return [
            self::TYPE_FULL_TIME => 'bi-briefcase',
            self::TYPE_PART_TIME => 'bi-clock-history',
            self::TYPE_LOCUM => 'bi-arrow-repeat',
            self::TYPE_TRAINING => 'bi-mortarboard',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    public function typeIcon(): string
    {
        return self::typeIcons()[$this->type] ?? 'bi-briefcase';
    }

    public function salaryLabel(): string
    {
        return $this->salary ?: 'يُحدد عند المقابلة';
    }

    /** Requirements are entered one per line and listed as bullets on the site. */
    public function requirementLines(): array
    {
        return collect(preg_split('/\r\n|\r|\n/', (string) $this->requirements))
            ->map(fn (string $line): string => trim(Str::of($line)->ltrim('-•* ')))
            ->filter()
            ->values()
            ->all();
    }

    /** Days left to apply, or null when the vacancy has no deadline. */
    public function daysLeft(): ?int
    {
        if (! $this->closes_at) {
            return null;
        }

        return (int) Carbon::today()->diffInDays($this->closes_at, false);
    }

    public function isClosingSoon(): bool
    {
        $days = $this->daysLeft();

        return $days !== null && $days <= 7;
    }
}
