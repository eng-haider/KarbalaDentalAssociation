<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class TransactionStatus extends Model
{
    /**
     * Badge colours offered to admins, keyed by the Filament colour name.
     * The public site maps these through TransactionStatus::publicColors().
     */
    public const COLORS = [
        'warning' => 'أصفر — قيد العمل',
        'success' => 'أخضر — مكتملة',
        'danger' => 'أحمر — مرفوضة أو متوقفة',
        'info' => 'أزرق — قيد المراجعة',
        'gray' => 'رمادي — محايد',
    ];

    protected $fillable = [
        'slug', 'name', 'color', 'icon', 'is_default', 'is_active', 'sort_order',
    ];

    protected $attributes = [
        'color' => 'gray',
        'icon' => 'bi-circle',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $status): void {
            if (blank($status->slug)) {
                $status->slug = self::generateSlug($status->name);
            }
        });

        // Only one status may be the default for new transactions.
        static::saved(function (self $status): void {
            if ($status->is_default) {
                static::whereKeyNot($status->getKey())
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * Arabic names slugify to an empty string, so fall back to a short random
     * key. The slug is an internal identifier — admins only ever see the name.
     */
    public static function generateSlug(?string $name): string
    {
        $slug = Str::slug((string) $name);

        if (blank($slug)) {
            $slug = 'status-'.Str::lower(Str::random(6));
        }

        while (static::where('slug', $slug)->exists()) {
            $slug = Str::limit($slug, 40, '').'-'.Str::lower(Str::random(4));
        }

        return $slug;
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class, 'status', 'slug');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Bootstrap background class used by the public transaction search. */
    public function publicColorClass(): string
    {
        return match ($this->color) {
            'warning' => 'bg-warning',
            'success' => 'bg-success',
            'danger' => 'bg-danger',
            'info' => 'bg-info',
            default => 'bg-secondary',
        };
    }
}
