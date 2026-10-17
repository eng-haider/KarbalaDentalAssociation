<?php

namespace App\Filament\Resources\Companies\RelationManagers;

use App\Filament\Resources\Companies\Schemas\CompanyProductForm;
use App\Filament\Resources\Companies\Tables\CompanyProductsTable;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    protected static ?string $title = 'المنتجات والعروض';

    protected static ?string $modelLabel = 'منتج';

    protected static ?string $pluralModelLabel = 'المنتجات';

    public function form(Schema $schema): Schema
    {
        return CompanyProductForm::configure($schema);
    }

    public function table(Table $table): Table
    {
        return CompanyProductsTable::configure($table)
            ->headerActions([
                CreateAction::make()
                    ->label('إضافة منتج')
                    ->icon(Heroicon::OutlinedPlus),
            ]);
    }
}
