<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

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

    protected static function booted(): void
    {
        static::creating(function (self $registration): void {
            $registration->invitation_token ??= Str::random(40);
        });

        // Card files uploaded from the dashboard go with the registration.
        static::deleted(function (self $registration): void {
            if (filled($registration->invitation_token)) {
                Storage::disk('public')->deleteDirectory('osif-invitations/'.$registration->invitation_token);
            }
        });
    }

    public function event(): BelongsTo
    {
        return $this->belongsTo(Event::class);
    }

    /** Registrations made before tokens existed get one on first use; the first writer wins. */
    public function invitationToken(): string
    {
        if (blank($this->invitation_token)) {
            static::query()->whereKey($this->getKey())->whereNull('invitation_token')
                ->update(['invitation_token' => Str::random(40)]);

            $this->invitation_token = static::query()->whereKey($this->getKey())->value('invitation_token');
            $this->syncOriginalAttribute('invitation_token');
        }

        return $this->invitation_token;
    }

    /** The attendee's invitation page — what the card's QR code opens. */
    public function invitationUrl(): string
    {
        return route('osif.invitation', $this->invitationToken());
    }

    /**
     * WhatsApp may keep an earlier failed preview for the bare invitation URL.
     * Its shared version is a distinct, stable URL while the QR code remains
     * short and clean.
     */
    public function invitationShareUrl(): string
    {
        return $this->invitationUrl().'?share=1';
    }

    /** Where the dashboard stores this attendee's card files on the public disk. */
    public function invitationFilePath(string $file): string
    {
        return 'osif-invitations/'.$this->invitationToken().'/'.$file;
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
