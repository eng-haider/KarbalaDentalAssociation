<?php

namespace App\Filament\Resources\Discounts\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DiscountsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('brand')
                    ->label('الجهة')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('title')
                    ->label('العرض')
                    ->limit(45)
                    ->searchable(),
                TextColumn::make('value_label')
                    ->label('قيمة الخصم')
                    ->badge()
                    ->color('warning'),
                IconColumn::make('is_active')
                    ->label('مفعّل')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->filters([
                TernaryFilter::make('is_active')->label('مفعّل'),
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
