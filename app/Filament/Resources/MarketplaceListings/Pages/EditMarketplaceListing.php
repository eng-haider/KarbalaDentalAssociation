<?php

namespace App\Filament\Resources\MarketplaceListings\Pages;

use App\Filament\Resources\MarketplaceListings\MarketplaceListingResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditMarketplaceListing extends EditRecord
{
    protected static string $resource = MarketplaceListingResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
