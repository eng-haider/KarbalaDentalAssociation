<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value'];

    protected static function booted(): void
    {
        // Keep the cached map in sync with any write.
        static::saved(fn () => Cache::forget('settings.all'));
        static::deleted(fn () => Cache::forget('settings.all'));
    }

    /** All settings as a key => value map, cached until the next write. */
    public static function allCached(): Collection
    {
        // Cache a plain array (serialises cleanly) and rebuild the Collection.
        $map = Cache::rememberForever('settings.all', fn () => static::query()->pluck('value', 'key')->all());

        return collect($map);
    }

    public static function get(string $key, ?string $default = null): ?string
    {
        $value = static::allCached()->get($key);

        return filled($value) ? $value : $default;
    }

    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value]);
    }
}
