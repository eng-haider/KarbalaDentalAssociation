<?php

use App\Providers\AppServiceProvider;
use App\Providers\Filament\AdminPanelProvider;
use App\Providers\Filament\CompanyPanelProvider;
use App\Providers\Filament\OsifPanelProvider;

return [
    AppServiceProvider::class,
    AdminPanelProvider::class,
    OsifPanelProvider::class,
    CompanyPanelProvider::class,
];
