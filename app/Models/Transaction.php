<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Transaction extends Model
{
    public const STATUS_PENDING = 'pending';

    public const STATUS_COMPLETED = 'completed';

    protected $fillable = ['name', 'transaction_type', 'status'];

    protected static function booted(): void
    {
        static::creating(function (self $transaction): void {
            if (blank($transaction->status)) {
                $transaction->status = static::defaultStatus();
            }
        });
    }

    public function statusModel(): BelongsTo
    {
        return $this->belongsTo(TransactionStatus::class, 'status', 'slug');
    }

    /**
     * Status slugs mapped to their Arabic labels, as defined in the dashboard.
     */
    public static function statuses(): array
    {
        return TransactionStatus::query()
            ->active()
            ->orderBy('sort_order')
            ->pluck('name', 'slug')
            ->all();
    }

    public static function defaultStatus(): string
    {
        return TransactionStatus::query()->where('is_default', true)->value('slug')
            ?? TransactionStatus::query()->active()->orderBy('sort_order')->value('slug')
            ?? self::STATUS_COMPLETED;
    }

    /**
     * Label for this transaction's status, falling back to the raw slug when the
     * status has since been renamed or removed from the dashboard.
     */
    public function statusLabel(): string
    {
        return $this->statusModel?->name ?? (string) $this->status;
    }

    /**
     * Clinic categories charted on the public analytics card, in slice order.
     */
    public static function categories(): array
    {
        return [
            'renewal' => ['label' => 'تجديد سنوي',   'color' => '#0C2D6B'],
            'join'    => ['label' => 'انتماء جديد',  'color' => '#C89A2B'],
            'open'    => ['label' => 'فتح عيادة',    'color' => '#15803D'],
            'close'   => ['label' => 'غلق عيادة',    'color' => '#B91C1C'],
            'other'   => ['label' => 'معاملات أخرى', 'color' => '#64748B'],
        ];
    }

    /**
     * Transaction types are free text typed by the staff, so a transaction is
     * placed by keyword. Opening/closing a clinic outranks renewal because a
     * type such as "تجديد ممارسه مع عيادة + غلق عيادة" is filed for the clinic
     * change; every unmatched type falls through to "other" so the categories
     * stay mutually exclusive and always add up to the total.
     */
    public static function categorise(string $type): string
    {
        return match (true) {
            str_contains($type, 'غلق') => 'close',
            str_contains($type, 'فتح') => 'open',
            str_contains($type, 'تجديد') => 'renewal',
            str_contains($type, 'انتماء') => 'join',
            default => 'other',
        };
    }

    /**
     * Category breakdown + headline totals behind the public analytics card.
     */
    public static function analytics(): array
    {
        $categories = static::categories();
        $counts = array_fill_keys(array_keys($categories), 0);

        static::query()
            ->selectRaw('transaction_type, count(*) as aggregate')
            ->groupBy('transaction_type')
            ->pluck('aggregate', 'transaction_type')
            ->each(function (int $aggregate, string $type) use (&$counts): void {
                $counts[static::categorise($type)] += $aggregate;
            });

        $total = array_sum($counts);

        $slices = [];
        foreach ($categories as $key => $category) {
            if ($counts[$key] === 0) {
                continue;
            }

            $slices[] = $category + [
                'key' => $key,
                'count' => $counts[$key],
                'percent' => round($counts[$key] / $total * 100, 1),
            ];
        }

        return [
            'slices' => $slices,
            'total' => $total,
            'doctors' => static::query()->distinct()->count('name'),
            'completed' => static::query()->where('status', self::STATUS_COMPLETED)->count(),
            'pending' => static::query()->where('status', self::STATUS_PENDING)->count(),
        ];
    }
}
