<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class CompanyProduct extends Model
{
    protected $fillable = [
        'company_id', 'name', 'product_category_id', 'description', 'image',
        'price', 'offer_price', 'offer_note', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'offer_price' => 'integer',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::deleted(function (self $product): void {
            if ($product->image) {
                Storage::disk('osif')->delete($product->image);
            }
        });
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ProductCategory::class, 'product_category_id');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /** Products with an offer, matching hasOffer(). */
    public function scopeWithOffer(Builder $query): Builder
    {
        return $query->where(fn (Builder $query) => $query
            ->where(fn (Builder $query) => $query->whereNotNull('price')->whereNotNull('offer_price')->whereColumn('offer_price', '<', 'price'))
            ->orWhere(fn (Builder $query) => $query->whereNotNull('offer_note')->where('offer_note', '!=', '')));
    }

    /** An offer is a lower price, a note such as "10 + 1 free", or both. */
    public function hasOffer(): bool
    {
        return $this->hasDiscount() || filled($this->offer_note);
    }

    public function hasDiscount(): bool
    {
        return $this->price && $this->offer_price !== null && $this->offer_price < $this->price;
    }

    /** What the doctor pays, or null when the price is given on request. */
    public function currentPrice(): ?int
    {
        return $this->hasDiscount() ? $this->offer_price : $this->price;
    }

    public function discountPercent(): ?int
    {
        return $this->hasDiscount() ? (int) round(100 - $this->offer_price * 100 / $this->price) : null;
    }

    /** Its company's page, opened on this product. */
    public function url(): string
    {
        return route('osif.companies.show', [$this->company, 'product' => $this->id]);
    }

    /** @return array{title: string, text: string, url: string} what a «مشاركة» button sends */
    public function shareData(): array
    {
        $offer = match (true) {
            $this->hasDiscount() => 'بخصم '.$this->discountPercent().'٪: '.static::formatPrice($this->offer_price),
            $this->price !== null => static::formatPrice($this->price),
            default => null,
        };

        return [
            'title' => $this->name,
            'text' => collect([$this->name.' من '.$this->company->name, $offer, $this->offer_note])->filter()->implode(' · '),
            'url' => $this->url(),
        ];
    }

    public function imageUrl(): ?string
    {
        return $this->image ? Storage::disk('osif')->url($this->image) : null;
    }

    public static function formatPrice(?int $amount): string
    {
        return $amount === null ? 'السعر عند الطلب' : number_format($amount).' د.ع';
    }
}
