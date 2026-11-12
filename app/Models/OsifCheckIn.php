<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** A guest's entry into one of the conference halls, scanned from their invitation's QR code. */
class OsifCheckIn extends Model
{
    /** The conference halls, each with its own scanner at the door. */
    public const HALLS = [
        1 => 'القاعة الأولى',
        2 => 'القاعة الثانية',
    ];

    public $timestamps = false;

    protected $fillable = [
        'event_registration_id',
        'hall',
        'user_id',
        'checked_in_at',
    ];

    protected function casts(): array
    {
        return [
            'hall' => 'integer',
            'checked_in_at' => 'datetime',
        ];
    }

    public static function hallName(?int $hall): ?string
    {
        return self::HALLS[$hall] ?? null;
    }

    public function registration(): BelongsTo
    {
        return $this->belongsTo(EventRegistration::class, 'event_registration_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** In Karbala's time: «9:12 صباحاً». */
    public function time(): string
    {
        return EventRegistration::karbalaTime($this->checked_in_at);
    }
}
