<?php

namespace App\Filament\Resources\Courses\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                ImageColumn::make('image')
                    ->label('الصورة')
                    ->circular(),
                TextColumn::make('title')
                    ->label('الدورة')
                    ->searchable()
                    ->limit(50)
                    ->weight('bold'),
                TextColumn::make('instructor')
                    ->label('المحاضر')
                    ->searchable()
                    ->toggleable(),
                TextColumn::make('starts_at')
                    ->label('تبدأ في')
                    ->dateTime('Y-m-d')
                    ->sortable(),
                TextColumn::make('seats')
                    ->label('المقاعد')
                    ->numeric()
                    ->toggleable(),
                IconColumn::make('is_published')
                    ->label('منشورة')
                    ->boolean(),
            ])
            ->defaultSort('starts_at', 'desc')
            ->filters([
                TernaryFilter::make('is_published')->label('منشورة'),
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
