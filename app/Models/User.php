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

#[Fillable(['name', 'email', 'role', 'company_id', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    public const ROLE_ADMIN = 'admin';

    public const ROLE_OSIF_COORDINATOR = 'osif_coordinator';

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
        ];
    }

    /** @return array<string, string> */
    public static function roleOptions(): array
    {
        return [
            self::ROLE_ADMIN => 'مدير النظام',
            self::ROLE_OSIF_COORDINATOR => 'منسق OSIF',
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
        return ($this->role ?? self::ROLE_ADMIN) === self::ROLE_ADMIN;
    }

    public function canManageOsif(): bool
    {
        return $this->isAdmin() || $this->role === self::ROLE_OSIF_COORDINATOR;
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return match ($panel->getId()) {
            'osif' => $this->canManageOsif(),
            'company' => $this->isCompany() && $this->company_id !== null,
            default => $this->isAdmin(),
        };
    }
}
