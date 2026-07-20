<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->label('الفعالية')
                    ->searchable()
                    ->limit(55)
                    ->weight('bold'),
                TextColumn::make('starts_at')
                    ->label('تبدأ في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
                TextColumn::make('location')
                    ->label('المكان')
                    ->toggleable(),
                IconColumn::make('is_featured')
                    ->label('في البانر')
                    ->boolean(),
                TextColumn::make('registrations_count')
                    ->label('التسجيلات')
                    ->counts('registrations')
                    ->badge(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                TernaryFilter::make('is_featured')->label('في البانر'),
                TernaryFilter::make('registration_open')->label('التسجيل مفتوح'),
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
