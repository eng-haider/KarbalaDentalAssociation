<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'title', 'slug', 'description', 'image', 'instructor',
        'starts_at', 'duration', 'seats', 'is_published',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'is_published' => 'boolean',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
