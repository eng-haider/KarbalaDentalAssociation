<?php

namespace App\Providers\Filament;

use App\Filament\Pages\OsifRegistrationSettings;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Widgets\OsifRegistrationStatsWidget;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Widgets\AccountWidget;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\PreventRequestForgery;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class OsifPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('osif')
            ->path('osif-dashboard')
            ->login()
            ->brandName('OSIF KARBALA')
            ->colors([
                'primary' => Color::Violet,
            ])
            ->font('Cairo', url: fn (): string => asset('fonts/cairo/cairo.css'), provider: LocalFontProvider::class)
            ->resources([
                EventRegistrationResource::class,
            ])
            ->pages([
                Dashboard::class,
                OsifRegistrationSettings::class,
            ])
            ->widgets([
                OsifRegistrationStatsWidget::class,
                AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                PreventRequestForgery::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ]);
    }
}
