<?php

namespace App\Filament\Resources\JobOpenings\Tables;

use App\Models\JobOpening;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class JobOpeningsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('logo')
                    ->label('الشعار')
                    ->square(),
                TextColumn::make('title')
                    ->label('العنوان الوظيفي')
                    ->description(fn (JobOpening $record): string => $record->employer)
                    ->wrap()
                    ->searchable(),
                TextColumn::make('type')
                    ->label('نوع الدوام')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => JobOpening::types()[$state] ?? $state)
                    ->color(fn (string $state): string => match ($state) {
                        JobOpening::TYPE_FULL_TIME => 'success',
                        JobOpening::TYPE_PART_TIME => 'info',
                        JobOpening::TYPE_LOCUM => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('specialty')
                    ->label('التخصص')
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('city')
                    ->label('المدينة')
                    ->placeholder('—'),
                TextColumn::make('closes_at')
                    ->label('آخر موعد')
                    ->date('Y-m-d')
                    ->placeholder('مفتوحة')
                    ->color(fn (JobOpening $record): string => $record->isClosingSoon() ? 'danger' : 'gray')
                    ->sortable(),
                IconColumn::make('is_featured')
                    ->label('مميزة')
                    ->boolean(),
                IconColumn::make('is_active')
                    ->label('منشورة')
                    ->boolean(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('أُضيفت في')
                    ->dateTime('Y-m-d H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('type')
                    ->label('نوع الدوام')
                    ->options(JobOpening::types()),
                TernaryFilter::make('is_active')
                    ->label('منشورة'),
                Filter::make('expired')
                    ->label('منتهية المدة')
                    ->query(fn (Builder $query): Builder => $query->whereDate('closes_at', '<', now()->toDateString())),
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
