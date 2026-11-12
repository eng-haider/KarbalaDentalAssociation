<?php

namespace App\Providers\Filament;

use App\Filament\Pages\OsifRegistrationSettings;
use App\Filament\Resources\Companies\CompanyResource;
use App\Filament\Resources\EventRegistrations\EventRegistrationResource;
use App\Filament\Widgets\OsifHallAttendanceWidget;
use App\Filament\Widgets\OsifProfessionalTitleCountsWidget;
use App\Filament\Widgets\OsifRegistrationStatsWidget;
use App\Http\Middleware\SendOsifScannersToCheckIn;
use Filament\FontProviders\LocalFontProvider;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\AuthenticateSession;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Navigation\NavigationItem;
use Filament\Pages\Dashboard;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Support\Colors\Color;
use Filament\Support\Icons\Heroicon;
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
                CompanyResource::class,
            ])
            // The conference-day QR scanner at the gate (a plain page, not part of the panel).
            ->navigationItems([
                NavigationItem::make('تسجيل الحضور (QR)')
                    ->url(fn (): string => route('osif.checkin'))
                    ->icon(Heroicon::OutlinedQrCode)
                    ->sort(-1),
            ])
            ->pages([
                Dashboard::class,
                OsifRegistrationSettings::class,
            ])
            ->widgets([
                OsifRegistrationStatsWidget::class,
                OsifHallAttendanceWidget::class,
                OsifProfessionalTitleCountsWidget::class,
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
                SendOsifScannersToCheckIn::class,
            ]);
    }
}
