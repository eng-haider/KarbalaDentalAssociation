<?php

namespace App\Filament\Resources\ProductCategories;

use App\Filament\Resources\ProductCategories\Pages\ManageProductCategories;
use App\Models\ProductCategory;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Facades\Filament;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** The categories OSIF companies choose from for their products; kept by the organisers. */
class ProductCategoryResource extends Resource
{
    protected static ?string $model = ProductCategory::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTag;

    protected static ?int $navigationSort = 5;

    protected static ?string $modelLabel = 'تصنيف';

    protected static ?string $pluralModelLabel = 'تصنيفات المنتجات';

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label('اسم التصنيف')
                    ->required()
                    ->maxLength(60)
                    ->unique(ignoreRecord: true)
                    ->placeholder('مثال: زراعة الأسنان'),
            ])
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
                    ->modalDescription('منتجات هذا التصنيف لن تُحذف، وستظهر بلا تصنيف حتى تختار الشركة تصنيفاً آخر.'),
            ])
            ->emptyStateHeading('لا توجد تصنيفات بعد');
    }

    public static function getNavigationGroup(): string|UnitEnum|null
    {
        return Filament::getCurrentPanel()?->getId() === 'osif' ? null : 'الوارد';
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function canEdit(Model $record): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function canDelete(Model $record): bool
    {
        return auth()->user()?->canManageOsif() ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProductCategories::route('/'),
        ];
    }
}
