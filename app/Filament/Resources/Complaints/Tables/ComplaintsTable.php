<?php

namespace App\Filament\Resources\Complaints\Tables;

use App\Models\Complaint;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class ComplaintsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('body')
                    ->label('نص الشكوى')
                    ->limit(80)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => Complaint::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        Complaint::STATUS_NEW => 'danger',
                        Complaint::STATUS_IN_REVIEW => 'warning',
                        default => 'success',
                    }),
                TextColumn::make('created_at')
                    ->label('وردت في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(Complaint::statuses()),
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
