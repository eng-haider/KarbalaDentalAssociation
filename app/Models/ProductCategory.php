<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** One of a company's own product categories, e.g. «زراعة الأسنان». */
class ProductCategory extends Model
{
    protected $fillable = ['company_id', 'name', 'sort_order'];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(CompanyProduct::class);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
