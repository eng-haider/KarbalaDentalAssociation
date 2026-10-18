<?php

namespace App\Filament\Company\Resources\Categories;

use App\Filament\Company\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Companies\Schemas\CompanyProductForm;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** The signed-in company's own product categories; other companies' are never reachable. */
class CategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'تصنيفاتي';

    protected static ?string $modelLabel = 'تصنيف';

    protected static ?string $pluralModelLabel = 'تصنيفاتي';

    protected static ?string $slug = 'categories';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([CompanyProductForm::categoryNameInput()])
            ->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('التصنيف')
                    ->weight('bold')
                    ->searchable(),
                TextColumn::make('products_count')
                    ->label('عدد المنتجات')
                    ->counts('products')
                    ->badge(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    ->modalDescription('منتجات هذا التصنيف لن تُحذف، وستظهر بلا تصنيف حتى تختار لها تصنيفاً آخر.'),
            ])
            ->emptyStateIcon(Heroicon::OutlinedTag)
            ->emptyStateHeading('لا توجد تصنيفات بعد')
            ->emptyStateDescription('أضف تصنيفات مثل «زراعة الأسنان» أو «أدوات جراحية»، ثم اختر لكل منتج تصنيفه.');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->where('company_id', auth()->user()?->company_id);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCategories::route('/'),
        ];
    }
}
