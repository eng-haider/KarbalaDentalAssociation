<?php

namespace App\Models;

use App\Support\WhatsApp;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EventRegistration extends Model
{
    /** Titles the invitation card shows as plain «طبيب أسنان»; only the specialist keeps theirs. */
    public const TITLES_SHOWN_AS_DENTIST = [
        'طبيب أسنان مقيم أقدم',
        'طبيب أسنان مقيم',
        'طبيب أسنان تدرج',
        'طبيب أسنان خريج جديد',
    ];

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
        'is_vip',
    ];

    protected function casts(): array
    {
        return [
            'is_handled' => 'boolean',
            'whatsapp_consent' => 'boolean',
            'owns_clinic' => 'boolean',
            'invitation_sent_at' => 'datetime',
            'is_vip' => 'boolean',
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
        // A new query value makes WhatsApp request the updated Open Graph
        // card instead of reusing its earlier wide-preview cache.
        return $this->invitationUrl().'?share=2';
    }

    /** Where the dashboard stores this attendee's card files on the public disk. */
    public function invitationFilePath(string $file): string
    {
        return 'osif-invitations/'.$this->invitationToken().'/'.$file;
    }

    /** The number in the international form wa.me expects. */
    public function whatsappNumber(): ?string
    {
        return WhatsApp::number($this->whatsapp_phone ?: $this->phone);
    }

    /** The professional title as the invitation card shows it; the registration keeps the full one. */
    public function cardTitle(): ?string
    {
        // «اسنان» or «أسنان», «اقدم» or «أقدم»: the same title however it was typed.
        $normalise = fn (?string $title): string => Str::squish(str_replace(['أ', 'إ', 'آ'], 'ا', (string) $title));

        return in_array($normalise($this->professional_title), array_map($normalise, self::TITLES_SHOWN_AS_DENTIST), true)
            ? 'طبيب أسنان'
            : $this->professional_title;
    }

    public function invitationCode(): string
    {
        return 'OSIF-'.str_pad((string) $this->getKey(), 4, '0', STR_PAD_LEFT);
    }
}
