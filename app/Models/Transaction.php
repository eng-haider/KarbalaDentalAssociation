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
}
