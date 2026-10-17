<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Models\CompanyProduct;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CompanyProductsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->disk('osif')
                    ->square()
                    ->imageSize(56),
                TextColumn::make('name')
                    ->label('المنتج')
                    ->weight(FontWeight::Bold)
                    ->description(fn (CompanyProduct $record): ?string => Str::limit((string) $record->description, 70) ?: null)
                    ->searchable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->state(fn (CompanyProduct $record): string => CompanyProduct::formatPrice($record->price)),
                ToggleColumn::make('is_active')
                    ->label('ظاهر'),
                TextColumn::make('offer')
                    ->label('العرض')
                    ->state(fn (CompanyProduct $record): ?string => collect([
                        $record->hasDiscount() ? CompanyProduct::formatPrice($record->offer_price).' (−'.$record->discountPercent().'٪)' : null,
                        $record->offer_note,
                    ])->filter()->implode(' · ') ?: null)
                    ->badge()
                    ->color('warning')
                    ->icon(Heroicon::Tag)
                    ->placeholder('—')
                    ->wrap(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('offers')
                    ->label('العروض')
                    ->trueLabel('عليها عرض')
                    ->falseLabel('بدون عرض')
                    ->queries(
                        true: fn (Builder $query) => $query->withOffer(),
                        false: fn (Builder $query) => $query->whereNot(fn (Builder $query) => $query->withOffer()),
                    ),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->emptyStateHeading('لا توجد منتجات بعد')
            ->emptyStateDescription('أضف المنتج الأول ليظهر في صفحة الشركة.');
    }
}
