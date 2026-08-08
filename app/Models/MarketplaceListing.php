<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class MarketplaceListing extends Model
{
    public const TYPE_SALE = 'sale';

    public const TYPE_WANTED = 'wanted';

    public const STATUS_PENDING = 'pending';

    public const STATUS_PUBLISHED = 'published';

    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'type', 'title', 'description', 'category', 'price',
        'contact_name', 'contact_phone', 'city', 'image', 'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
        ];
    }

    /**
     * Public submissions are held for review, so only what the dashboard has
     * approved is ever listed on the site.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_PUBLISHED);
    }

    public function scopeOfType(Builder $query, ?string $type): Builder
    {
        return $query->when($type, fn (Builder $q) => $q->where('type', $type));
    }

    /** Listing kinds: something offered, or something a member is looking for. */
    public static function types(): array
    {
        return [
            self::TYPE_SALE => 'للبيع',
            self::TYPE_WANTED => 'مطلوب للشراء',
        ];
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'بانتظار المراجعة',
            self::STATUS_PUBLISHED => 'منشور',
            self::STATUS_REJECTED => 'مرفوض',
        ];
    }

    public static function categories(): array
    {
        return [
            'devices' => 'أجهزة ومعدات',
            'materials' => 'مواد وخامات',
            'furniture' => 'أثاث عيادة',
            'clinic' => 'عيادة كاملة',
            'other' => 'أخرى',
        ];
    }

    public function typeLabel(): string
    {
        return self::types()[$this->type] ?? $this->type;
    }

    public function categoryLabel(): ?string
    {
        return self::categories()[$this->category] ?? null;
    }

    /**
     * Price is stored in whole dinars; a listing without one is shown as
     * negotiable rather than as a zero.
     */
    public function priceLabel(): string
    {
        return $this->price ? number_format($this->price).' د.ع' : 'السعر عند التواصل';
    }
}
