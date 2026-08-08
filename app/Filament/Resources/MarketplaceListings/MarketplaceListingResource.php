<?php

namespace App\Filament\Resources\MarketplaceListings;

use App\Filament\Resources\MarketplaceListings\Pages\EditMarketplaceListing;
use App\Filament\Resources\MarketplaceListings\Pages\ListMarketplaceListings;
use App\Filament\Resources\MarketplaceListings\Schemas\MarketplaceListingForm;
use App\Filament\Resources\MarketplaceListings\Tables\MarketplaceListingsTable;
use App\Models\MarketplaceListing;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class MarketplaceListingResource extends Resource
{
    protected static ?string $model = MarketplaceListing::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'الوارد';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'إعلان';

    protected static ?string $pluralModelLabel = 'إعلانات البيع والشراء';

    public static function form(Schema $schema): Schema
    {
        return MarketplaceListingForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return MarketplaceListingsTable::configure($table);
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) (MarketplaceListing::where('status', MarketplaceListing::STATUS_PENDING)->count() ?: '');
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListMarketplaceListings::route('/'),
            'edit' => EditMarketplaceListing::route('/{record}/edit'),
        ];
    }
}
