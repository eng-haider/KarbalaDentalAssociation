<?php

namespace App\Filament\Resources\EventRegistrations\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class EventRegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('phone')
                    ->label('الهاتف')
                    ->searchable(),
                TextColumn::make('membership_number')
                    ->label('رقم العضوية')
                    ->searchable(),
                TextColumn::make('event.title')
                    ->label('الفعالية')
                    ->limit(40),
                IconColumn::make('is_handled')
                    ->label('تمت المعالجة')
                    ->boolean(),
                TextColumn::make('created_at')
                    ->label('سُجّل في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('event_id')
                    ->label('الفعالية')
                    ->relationship('event', 'title'),
                TernaryFilter::make('is_handled')->label('تمت المعالجة'),
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
