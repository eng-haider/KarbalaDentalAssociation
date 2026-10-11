<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EventRegistration extends Model
{
    protected $fillable = [
        'event_id',
        'name',
        'phone',
        'whatsapp_phone',
        'email',
        'membership_number',
        'professional_title',
        'owns_clinic',
        'workplace',
        'city',
        'attendance_type',
        'notes',
        'whatsapp_consent',
        'is_handled',
        'invitation_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'is_handled' => 'boolean',
            'whatsapp_consent' => 'boolean',
            'owns_clinic' => 'boolean',
            'invitation_sent_at' => 'datetime',
        ];
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /**
     * The number in the international form wa.me expects
     * (07801234567 → 9647801234567).
     */
    public function whatsappNumber(): ?string
    {
        $digits = preg_replace('/\D+/', '', (string) ($this->whatsapp_phone ?: $this->phone));

        if ($digits === '') {
            return null;
        }

        if (str_starts_with($digits, '00')) {
            $digits = substr($digits, 2);
        }

        // Iraqi numbers are usually typed locally (07…), sometimes as +964 07….
        if (str_starts_with($digits, '9640')) {
            return '964'.substr($digits, 4);
        }

        if (str_starts_with($digits, '0')) {
            return '964'.substr($digits, 1);
        }

        if (strlen($digits) === 10 && str_starts_with($digits, '7')) {
            return '964'.$digits;
        }

        return $digits;
    }

    public function invitationCode(): string
    {
        return 'OSIF-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }
}
