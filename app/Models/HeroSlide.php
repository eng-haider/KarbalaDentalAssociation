<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class HeroSlide extends Model
{
    protected $fillable = [
        'image', 'badge', 'badge_icon', 'title', 'subtitle',
        'button_label', 'button_url', 'button2_label', 'button2_url',
        'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true)->orderBy('sort_order');
    }

    /** Resolve the slide image whether it's an uploaded path or an absolute URL. */
    public function imageUrl(): ?string
    {
        if (blank($this->image)) {
            return null;
        }

        return str_starts_with($this->image, 'http') ? $this->image : Storage::url($this->image);
    }
}
