<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** The broadcast's message to one WhatsApp number. */
class OsifBroadcastMessage extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'osif_broadcast_id',
        'event_registration_id',
        'phone',
        'name',
        'claimed_at',
        'sent_at',
        'error',
    ];

    protected function casts(): array
    {
        return [
            'claimed_at' => 'datetime',
            'sent_at' => 'datetime',
        ];
    }

    public function broadcast(): BelongsTo
    {
        return $this->belongsTo(OsifBroadcast::class, 'osif_broadcast_id');
    }

    /** @return array{name: string, url: string} What the sending dialog needs for this number. */
    public function queueItem(): array
    {
        return [
            'name' => $this->name,
            'url' => route('osif.broadcasts.send', $this, absolute: false),
        ];
    }
}
