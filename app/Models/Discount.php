<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'brand', 'title', 'tag', 'description', 'image', 'logo', 'value_label',
        'value_caption', 'perks', 'link', 'icon', 'note', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'perks' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }
}
