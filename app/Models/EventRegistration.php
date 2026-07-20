<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = ['event_id', 'name', 'phone', 'membership_number', 'is_handled'];

    protected function casts(): array
    {
        return ['is_handled' => 'boolean'];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }
}
