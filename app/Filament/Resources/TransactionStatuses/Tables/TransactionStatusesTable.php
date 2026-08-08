<?php

namespace App\Filament\Resources\TransactionStatuses\Tables;

use App\Models\TransactionStatus;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TransactionStatusesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('الحالة')
                    ->badge()
                    ->color(fn (TransactionStatus $record): string => $record->color)
                    ->searchable(),
                TextColumn::make('transactions_count')
                    ->label('عدد المعاملات')
                    ->counts('transactions')
                    ->badge()
                    ->color('gray'),
                TextColumn::make('icon')
                    ->label('الأيقونة')
                    ->placeholder('bi-circle')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_default')
                    ->label('افتراضية')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('مفعّلة')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->recordActions([
                EditAction::make(),
                DeleteAction::make()
                    // Deleting a status in use would leave those transactions
                    // showing a label that no longer exists.
                    ->disabled(fn (TransactionStatus $record): bool => $record->transactions()->exists())
                    ->tooltip(fn (TransactionStatus $record): ?string => $record->transactions()->exists()
                        ? 'لا يمكن حذف حالة مستخدمة في معاملات قائمة.'
                        : null),
            ]);
    }
}
