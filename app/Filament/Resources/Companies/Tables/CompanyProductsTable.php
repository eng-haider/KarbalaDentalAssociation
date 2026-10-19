<?php

namespace App\Filament\Resources\Companies\Tables;

use App\Filament\Resources\Companies\Schemas\CompanyProductForm;
use App\Models\CompanyProduct;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Support\Enums\FontWeight;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Js;
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
                TextColumn::make('category.name')
                    ->label('التصنيف')
                    ->badge()
                    ->color('gray')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('price')
                    ->label('السعر')
                    ->state(fn (CompanyProduct $record): string => CompanyProduct::formatPrice($record->price)),
                ToggleColumn::make('is_active')
                    ->label('ظاهر'),
                TextColumn::make('offer')
                    ->label('العرض')
                    ->state(fn (CompanyProduct $record): ?string => collect([
                        $record->hasDiscount() ? 'خصم '.$record->discount_percent.'٪: '.CompanyProduct::formatPrice($record->currentPrice()) : null,
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
                SelectFilter::make('product_category_id')
                    ->label('التصنيف')
                    ->options(fn ($livewire): array => CompanyProductForm::companyOf($livewire)?->productCategories()->pluck('name', 'id')->all() ?? []),
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
                // A hidden product isn't on the page, so its link would show nothing.
                Action::make('share')
                    ->label('مشاركة')
                    ->icon(Heroicon::OutlinedShare)
                    ->color('gray')
                    ->visible(fn (CompanyProduct $record): bool => $record->is_active)
                    ->alpineClickHandler(fn (CompanyProduct $record): string => 'shareLink('.Js::from($record->shareData()).')'),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->emptyStateIcon(Heroicon::OutlinedShoppingBag)
            ->emptyStateHeading('لا توجد منتجات بعد')
            ->emptyStateDescription('أضف المنتج الأول ليظهر في صفحة الشركة.');
    }
}
