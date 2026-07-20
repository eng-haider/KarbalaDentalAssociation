<?php

namespace App\Filament\Resources\BoardMembers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BoardMembersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('photo')
                    ->label('الصورة')
                    ->circular(),
                TextColumn::make('name')
                    ->label('الاسم')
                    ->searchable()
                    ->weight('bold'),
                TextColumn::make('position')
                    ->label('المنصب')
                    ->searchable(),
                TextColumn::make('sort_order')
                    ->label('الترتيب')
                    ->numeric()
                    ->sortable(),
            ])
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
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
