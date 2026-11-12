<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Filament\Panel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'phone', 'role', 'osif_hall', 'company_id', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    public const ROLE_ADMIN = 'admin';

    /** An administrator who may also send OSIF invitations through the WhatsApp API. */
    public const ROLE_WHATSAPP_ADMIN = 'whatsapp_admin';

    public const ROLE_OSIF_COORDINATOR = 'osif_coordinator';

    /** Conference day: scans invitations at one hall's door and sees nothing else. */
    public const ROLE_OSIF_SCANNER = 'osif_scanner';

    /** An exhibiting company's login; it reaches only that company's dashboard. */
    public const ROLE_COMPANY = 'company';

    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'osif_hall' => 'integer',
        ];
    }

    /** @return array<string, string> */
    public static function roleOptions(): array
    {
        return [
            self::ROLE_ADMIN => 'مدير النظام',
            self::ROLE_WHATSAPP_ADMIN => 'مدير النظام (إرسال واتساب)',
            self::ROLE_OSIF_COORDINATOR => 'منسق OSIF',
            self::ROLE_OSIF_SCANNER => 'ماسح QR (قاعة)',
        ];
    }

    /** Every role's label, including company accounts (which are managed from the company itself). */
    public static function roleLabels(): array
    {
        return self::roleOptions() + [self::ROLE_COMPANY => 'حساب شركة'];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function isCompany(): bool
    {
        return $this->role === self::ROLE_COMPANY;
    }

    public function isAdmin(): bool
    {
        // Existing accounts created before roles were introduced retain their
        // administration access when the migration is deployed.
        return in_array($this->role ?? self::ROLE_ADMIN, [self::ROLE_ADMIN, self::ROLE_WHATSAPP_ADMIN], true);
    }

    /** Sending through the WhatsApp API costs money and reaches every attendee, so it is one role's alone. */
    public function canSendWhatsAppInvitations(): bool
    {
        return $this->role === self::ROLE_WHATSAPP_ADMIN;
    }

    public function canManageOsif(): bool
    {
        return $this->isAdmin() || $this->role === self::ROLE_OSIF_COORDINATOR;
    }

    public function isOsifScanner(): bool
    {
        return $this->role === self::ROLE_OSIF_SCANNER;
    }

    /** Who may scan invitations at the conference: the OSIF staff and the hall scanners. */
    public function canCheckInOsifGuests(): bool
    {
        return $this->canManageOsif() || $this->isOsifScanner();
    }

    /** The hall a scanner is fixed to; staff choose theirs on the scanning page. */
    public function checkInHall(): ?int
    {
        return $this->isOsifScanner() && isset(OsifCheckIn::HALLS[$this->osif_hall]) ? $this->osif_hall : null;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            // Scanners sign in through the OSIF login, which then sends them to the scanning page alone.
            'osif' => $this->canCheckInOsifGuests(),
            'company' => $this->isCompany() && $this->company_id !== null,
            default => $this->isAdmin(),
        };
    }
}
