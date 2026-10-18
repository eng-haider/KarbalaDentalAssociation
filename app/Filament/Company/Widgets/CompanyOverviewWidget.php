<?php

namespace App\Filament\Company\Widgets;

use App\Filament\Company\Pages\CompanyProfile;
use App\Filament\Company\Resources\Products\ProductResource;
use App\Models\Company;
use App\Support\QrCode;
use Filament\Widgets\Widget;
use Illuminate\Support\Js;

/** The company dashboard's home: its page link, what is listed, and the next steps. */
class CompanyOverviewWidget extends Widget
{
    protected string $view = 'filament.company.widgets.company-overview';

    protected int|string|array $columnSpan = 'full';

    protected static bool $isLazy = false;

    protected function getViewData(): array
    {
        /** @var Company $company */
        $company = auth()->user()->company;
        $products = $company->products()->get();

        return [
            'company' => $company,
            'pageUrl' => $pageUrl = route('osif.companies.show', $company),
            'share' => Js::from($company->shareData()),
            'qr' => QrCode::svg($pageUrl),
            'qrDownloadUrl' => route('osif.companies.qr', $company),
            'posterUrl' => route('osif.companies.poster', $company),
            'productsUrl' => ProductResource::getUrl(),
            'profileUrl' => CompanyProfile::getUrl(),
            'shown' => $products->where('is_active', true)->count(),
            'offers' => $products->where('is_active', true)->filter->hasOffer()->count(),
            'hidden' => $products->where('is_active', false)->count(),
            'profileDone' => filled($company->logo) && filled($company->description),
        ];
    }
}
