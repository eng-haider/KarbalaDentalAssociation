<?php

namespace App\Providers;

use Filament\Support\Facades\FilamentView;
use Filament\View\PanelsRenderHook;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Foundation\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // The «مشاركة» buttons in every dashboard (company pages and products).
        FilamentView::registerRenderHook(
            PanelsRenderHook::BODY_END,
            fn (): Htmlable => app(Vite::class)(['resources/js/dashboard-share.js']),
        );
    }
}
