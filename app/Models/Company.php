<?php

namespace App\Models;

use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * A company exhibiting at OSIF. It has a public page with its products and
 * offers, a dashboard account to manage them, and receives cart orders on
 * WhatsApp.
 */
class Company extends Model
{
    protected $fillable = [
        'name', 'slug', 'logo', 'cover', 'description',
        'whatsapp_phone', 'booth', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $company): void {
            $company->slug = static::uniqueSlug($company->slug ?: $company->name, $company->getKey());
        });

        static::deleting(function (self $company): void {
            $disk = Storage::disk('osif');
            $disk->delete(array_filter([$company->logo, $company->cover, ...$company->products()->pluck('image')]));
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function products(): HasMany
    {
        return $this->hasMany(CompanyProduct::class)->orderBy('sort_order')->orderBy('id');
    }

    /** The login that manages this company's page from the company dashboard. */
    public function productCategories(): HasMany
    {
        return $this->hasMany(ProductCategory::class)->orderBy('sort_order')->orderBy('name');
    }

    /** Adds a category at the end of the company's list. */
    public function addProductCategory(string $name): ProductCategory
    {
        return $this->productCategories()->create([
            'name' => $name,
            'sort_order' => (int) $this->productCategories()->max('sort_order') + 1,
        ]);
    }

    public function account(): HasOne
    {
        return $this->hasOne(User::class)->where('role', User::ROLE_COMPANY);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    /** The product and offer counts a company card shows. */
    public function scopeWithShopCounts(Builder $query): Builder
    {
        return $query->withCount([
            'products' => fn ($query) => $query->active(),
            'products as offers_count' => fn ($query) => $query->active()->withOffer(),
        ]);
    }

    public function logoUrl(): ?string
    {
        return $this->logo ? Storage::disk('osif')->url($this->logo) : null;
    }

    public function coverUrl(): ?string
    {
        return $this->cover ? Storage::disk('osif')->url($this->cover) : null;
    }

    public function whatsappNumber(): ?string
    {
        return WhatsApp::number($this->whatsapp_phone);
    }

    /** Shown in place of a missing logo: «Dental Pro» → DE, «النور» → ن. */
    public function initials(): string
    {
        $word = preg_replace('/^ال/u', '', Str::before(trim($this->name), ' ')) ?: trim($this->name);

        return preg_match('/^\p{Arabic}/u', $word)
            ? Str::substr($word, 0, 1)
            : Str::upper(Str::substr($word, 0, 2));
    }

    private static function uniqueSlug(string $value, ?int $ignoreId): string
    {
        $base = Str::slug($value) ?: 'company';
        $slug = $base;

        for ($n = 2; static::query()->where('slug', $slug)->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))->exists(); $n++) {
            $slug = $base.'-'.$n;
        }

        return $slug;
    }
}
