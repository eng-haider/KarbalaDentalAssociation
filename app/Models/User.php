<?php

namespace App\Models;

use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'role', 'password'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable implements FilamentUser
{
    public const ROLE_ADMIN = 'admin';
    public const ROLE_OSIF_COORDINATOR = 'osif_coordinator';

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
            default => $this->isAdmin(),
        };
    }
}
