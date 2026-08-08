<?php

namespace App\Filament\Resources\MarketplaceListings\Tables;

use App\Models\MarketplaceListing;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;

class MarketplaceListingsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->square(),
                TextColumn::make('title')
                    ->label('العنوان')
                    ->limit(40)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('النوع')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MarketplaceListing::types()[$state] ?? $state)
                    ->color(fn (string $state): string => $state === MarketplaceListing::TYPE_SALE ? 'success' : 'info'),
                TextColumn::make('category')
                    ->label('التصنيف')
                    ->formatStateUsing(fn (?string $state): string => MarketplaceListing::categories()[$state] ?? '—'),
                TextColumn::make('price')
                    ->label('السعر')
                    ->formatStateUsing(fn (?int $state): string => $state ? number_format($state).' د.ع' : '—'),
                TextColumn::make('contact_name')
                    ->label('صاحب الإعلان')
                    ->description(fn (MarketplaceListing $record): string => $record->contact_phone)
                    ->searchable(),
                TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => MarketplaceListing::statuses()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        MarketplaceListing::STATUS_PENDING => 'warning',
                        MarketplaceListing::STATUS_PUBLISHED => 'success',
                        default => 'danger',
                    }),
                TextColumn::make('created_at')
                    ->label('أُضيف في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('الحالة')
                    ->options(MarketplaceListing::statuses()),
                SelectFilter::make('type')
                    ->label('النوع')
                    ->options(MarketplaceListing::types()),
            ])
            ->recordActions([
                Action::make('approve')
                    ->label('نشر')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (MarketplaceListing $record): bool => $record->status !== MarketplaceListing::STATUS_PUBLISHED)
                    ->action(fn (MarketplaceListing $record) => $record->update(['status' => MarketplaceListing::STATUS_PUBLISHED])),
                Action::make('reject')
                    ->label('رفض')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (MarketplaceListing $record): bool => $record->status !== MarketplaceListing::STATUS_REJECTED)
                    ->action(fn (MarketplaceListing $record) => $record->update(['status' => MarketplaceListing::STATUS_REJECTED])),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    \Filament\Actions\BulkAction::make('approveSelected')
                        ->label('نشر المحدّد')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(fn (Collection $records) => $records->each->update(['status' => MarketplaceListing::STATUS_PUBLISHED]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
