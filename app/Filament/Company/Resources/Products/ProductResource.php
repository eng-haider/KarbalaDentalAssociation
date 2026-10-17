<?php

namespace App\Filament\Company\Resources\Products;

use App\Filament\Company\Resources\Products\Pages\ManageProducts;
use App\Filament\Resources\Companies\Schemas\CompanyProductForm;
use App\Filament\Resources\Companies\Tables\CompanyProductsTable;
use App\Models\CompanyProduct;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** The signed-in company's own products; other companies' products are never reachable. */
class ProductResource extends Resource
{
    protected static ?string $model = CompanyProduct::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'منتجاتي وعروضي';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'منتجاتي وعروضي';

    protected static ?string $slug = 'products';

    public static function form(Schema $schema): Schema
    {
        return CompanyProductForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CompanyProductsTable::configure($table);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', auth()->user()?->company_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }
}
