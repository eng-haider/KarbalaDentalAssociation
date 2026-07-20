<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class News extends Model
{
    protected $table = 'news';

    protected $fillable = [
        'title', 'slug', 'excerpt', 'body', 'image', 'video_url', 'is_published', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'is_published' => 'boolean',
            'published_at' => 'datetime',
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function hasVideo(): bool
    {
        return filled($this->video_url);
    }

    /**
     * Normalise the stored link into something a view can render as the main
     * media. Returns ['type' => 'iframe'|'file', 'url' => string] or null.
     * Supports YouTube, Vimeo, and direct video files (mp4/webm/ogg/mov).
     */
    public function videoEmbed(): ?array
    {
        $url = trim((string) $this->video_url);

        if ($url === '') {
            return null;
        }

        // YouTube: watch?v=ID, youtu.be/ID, /embed/ID, /shorts/ID
        if (preg_match('~(?:youtube\.com/(?:watch\?v=|embed/|shorts/)|youtu\.be/)([\w-]{11})~', $url, $m)) {
            return ['type' => 'iframe', 'url' => "https://www.youtube.com/embed/{$m[1]}"];
        }

        // Vimeo: vimeo.com/ID
        if (preg_match('~vimeo\.com/(?:video/)?(\d+)~', $url, $m)) {
            return ['type' => 'iframe', 'url' => "https://player.vimeo.com/video/{$m[1]}"];
        }

        // Direct video file (uploaded to the public disk or an absolute URL)
        if (preg_match('~\.(mp4|webm|ogg|mov)(\?.*)?$~i', $url)) {
            $src = str_starts_with($url, 'http') || str_starts_with($url, '/')
                ? $url
                : Storage::url($url);

            return ['type' => 'file', 'url' => $src];
        }

        // Unknown host but looks like a URL — let the browser try it in an iframe.
        return str_starts_with($url, 'http') ? ['type' => 'iframe', 'url' => $url] : null;
    }
}
