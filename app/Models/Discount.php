<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class Discount extends Model
{
    protected $fillable = [
        'brand', 'title', 'tag', 'description', 'body', 'image', 'logo', 'attachment', 'attachment_name',
        'value_label', 'value_caption', 'perks', 'link', 'icon', 'note', 'is_active', 'sort_order',
    ];

    /** The file types an offer can attach, by extension. */
    public const ATTACHMENT_TYPES = [
        'pdf' => 'application/pdf',
        'doc' => 'application/msword',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
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

    public function attachmentIsPdf(): bool
    {
        return strtolower(pathinfo((string) $this->attachment, PATHINFO_EXTENSION)) === 'pdf';
    }

    /** The name the file is downloaded under: as the admin uploaded it. */
    public function attachmentFileName(): string
    {
        return $this->attachment_name ?: basename((string) $this->attachment);
    }
}
